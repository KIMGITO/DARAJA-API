import React, { useState, useEffect, useMemo, useCallback } from 'react';
import { useApp } from '../context/AppContext';
import { ServiceItem, BarberProfile } from '../types';
import { 
  X, 
  Check, 
  Clock, 
  Calendar as CalendarIcon, 
  User, 
  Scissors, 
  ChevronRight, 
  ChevronLeft,
  Sparkles, 
  Phone, 
  Mail, 
  FileText,
  CheckCircle2,
  ExternalLink,
  MessageSquare,
  Smartphone,
  Loader2,
  AlertCircle,
  Lock
} from 'lucide-react';
import { DayPicker } from 'react-day-picker';
import { format, startOfDay, addDays } from 'date-fns';
import 'react-day-picker/style.css';
import { Button } from './ui/Button';
import { Input } from './ui/Input';
import { bookingService } from '../services/bookingService';
import { bookingEngineService } from '../services/bookingEngineService';
import { paymentService } from '../services/paymentService';
import { serviceService } from '../services/serviceService';

interface BookedSlotInfo {
  time_slot: string;
  end_time: string;
  status: string;
}

export const BookingModal: React.FC = () => {
  const { 
    isBookingModalOpen, 
    closeBookingModal, 
    services, 
    barbers, 
    selectedPreServiceId, 
    selectedPreBarberId, 
    businessInfo 
  } = useApp();

  const [step, setStep] = useState<1 | 2 | 3 | 4 | 5>(1);
  const [selectedServiceIds, setSelectedServiceIds] = useState<string[]>([]);
  const [selectedBarberId, setSelectedBarberId] = useState<string>('any');
  const [selectedDate, setSelectedDate] = useState<Date | undefined>();
  const [selectedTimeSlot, setSelectedTimeSlot] = useState<string>('');

  // Customer Details Form
  const [customerName, setCustomerName] = useState('');
  const [customerPhone, setCustomerPhone] = useState('');
  const [customerEmail, setCustomerEmail] = useState('');
  const [specialRequests, setSpecialRequests] = useState('');

  // Availability + Payment state
  const [availableSlots, setAvailableSlots] = useState<{ start_time: string; end_time: string }[]>([]);
  const [bookedSlots, setBookedSlots] = useState<BookedSlotInfo[]>([]);
  const [slotsLoading, setSlotsLoading] = useState(false);
  const [qualifiedStaff, setQualifiedStaff] = useState<{ staffId: string; staffName: string; providerType: string }[]>([]);

  // Payment flow state
  const [paymentStatus, setPaymentStatus] = useState<'idle' | 'pushing' | 'awaiting_pin' | 'confirmed' | 'failed'>('idle');
  const [paymentError, setPaymentError] = useState<string | null>(null);
  const [checkoutRequestId, setCheckoutRequestId] = useState<string | null>(null);
  const [paymentReceipt, setPaymentReceipt] = useState<string | null>(null);

  // Confirmed booking result
  const [confirmedBooking, setConfirmedBooking] = useState<any>(null);
  const [pendingBooking, setPendingBooking] = useState<any>(null);

  const selectedServices = services.filter(s => selectedServiceIds.includes(s.id));
  const totalPrice = selectedServices.reduce((sum, s) => sum + s.priceKsh, 0);
  const totalDuration = selectedServices.reduce((sum, s) => sum + s.durationMinutes, 0);
  const depositBreakdown = paymentService.calculateDeposit(totalPrice, 0);
  const depositKsh = depositBreakdown.minimumDepositKsh;

  /** Resolve a concrete provider id whenever 'any' is chosen:
   *  first active barber who can perform ALL selected services. */
  const canProviderHandle = useCallback((b: BarberProfile, serviceIds: string[]) => {
    if (serviceIds.length === 0) return true;
    return serviceIds.every(id => b.servicesOfferedIds.includes(id));
  }, []);

  const resolvedProvider = useMemo(() => {
    if (selectedBarberId !== 'any') return barbers.find(b => b.id === selectedBarberId) || null;
    return barbers.find(b => b.status !== 'inactive' && canProviderHandle(b, selectedServiceIds)) 
      || barbers.find(b => b.status !== 'inactive') 
      || null;
  }, [barbers, selectedBarberId, selectedServiceIds, canProviderHandle]);

  const barberDisplayName = selectedBarberId === 'any'
    ? resolvedProvider?.name || 'First Available Master Barber'
    : barbers.find(b => b.id === selectedBarberId)?.name || 'Assigned Master';

  // Initialize selections when modal opens
  useEffect(() => {
    if (isBookingModalOpen) {
      if (selectedPreServiceId) {
        setSelectedServiceIds([selectedPreServiceId]);
      } else if (selectedServiceIds.length === 0 && services.length > 0) {
        setSelectedServiceIds([services[0].id]);
      }

      if (selectedPreBarberId) {
        setSelectedBarberId(selectedPreBarberId);
      } else {
        setSelectedBarberId('any');
      }

      setSelectedDate(startOfDay(addDays(new Date(), 1)));
      setSelectedTimeSlot('');
      setPaymentStatus('idle');
      setPaymentError(null);
      setCheckoutRequestId(null);
      setPaymentReceipt(null);
      setConfirmedBooking(null);
      setPendingBooking(null);

      setStep(selectedPreServiceId && selectedPreBarberId ? 3 : selectedPreServiceId ? 2 : 1);
    }
  }, [isBookingModalOpen, selectedPreServiceId, selectedPreBarberId]);

  // Load available slots using the new booking engine
  useEffect(() => {
    if (!isBookingModalOpen || step !== 3 || !resolvedProvider || !selectedDate) return;

    const providerId = resolvedProvider.id;
    const dateStr = format(selectedDate, 'yyyy-MM-dd');
    setSlotsLoading(true);
    setBookedSlots([]);
    setAvailableSlots([]);

    Promise.all([
      bookingService.getBookedSlots(providerId, dateStr),
      // Use the new booking engine to get slots sized to the total service duration
      (async () => {
        try {
          const slots = await bookingEngineService.getAvailableSlots(
            selectedServiceIds[0],
            dateStr,
            [providerId]
          );
          // Convert to the format expected by the UI
          return slots.map(s => ({
            start_time: format(new Date(s.startTs), 'hh:mm a'),
            end_time: format(new Date(s.endTs), 'hh:mm a')
          }));
        } catch {
          return [] as { start_time: string; end_time: string }[];
        }
      })(),
      // Qualified staff for suggestions when no slots exist
      bookingEngineService.getQualifiedStaff(selectedServiceIds[0])
    ])
      .then(([booked, available, qualified]) => {
        setBookedSlots(booked);
        setAvailableSlots(available);
        setQualifiedStaff(qualified);
      })
      .catch(err => console.error('Failed to load slots:', err))
      .finally(() => setSlotsLoading(false));
  }, [isBookingModalOpen, step, resolvedProvider, selectedDate, totalDuration, selectedServiceIds]);

  const toggleService = (id: string) => {
    if (selectedServiceIds.includes(id)) {
      if (selectedServiceIds.length > 1) {
        setSelectedServiceIds(selectedServiceIds.filter(sId => sId !== id));
      }
    } else {
      setSelectedServiceIds([...selectedServiceIds, id]);
    }
  };

  /** Remove slots that conflict with already-booked/duration-overlapping bookings. */
  const isSlotBooked = (slotStart: string, slotEnd?: string): boolean => {
    const toMin = (t: string) => {
      // Accept "10:00 AM" or "10:00" formats
      const match = t.trim().match(/^(\d{1,2}):(\d{2})\s*(AM|PM)?$/i);
      if (!match) return 0;
      let h = parseInt(match[1], 10);
      const m = parseInt(match[2], 10);
      if (match[3]) {
        const ampm = match[3].toUpperCase();
        if (ampm === 'PM' && h !== 12) h += 12;
        if (ampm === 'AM' && h === 12) h = 0;
      }
      return h * 60 + m;
    };
    const slotStartMin = toMin(slotStart);
    const slotEndMin = slotEnd ? toMin(slotEnd) : slotStartMin + totalDuration;
    return bookedSlots.some(b => {
      const bStart = toMin(b.time_slot);
      const bEnd = b.end_time ? toMin(b.end_time) : bStart + 30;
      // Booked slot overlaps this candidate slot for its full duration window
      return slotStartMin < bEnd && slotEndMin > bStart;
    });
  };

  const formatSlotDisplay = (t: string): string => {
    const match = t.trim().match(/^(\d{1,2}):(\d{2})(?:\s*(AM|PM)?)?$/i);
    if (!match) return t;
    let h = parseInt(match[1], 10);
    const m = match[2];
    const suffix = match[3]?.toUpperCase();
    let display = t;
    if (!suffix) {
      const s = h >= 12 ? 'PM' : 'AM';
      const h12 = h % 12 === 0 ? 12 : h % 12;
      display = `${h12}:${m} ${s}`;
    }
    return display;
  };

  // Slots are generated by the database and sized to the total requested
  // service duration, so each slot's end_time spans the full reserved window.
  // The provider is guaranteed free for the entire [start_time, end_time].
  const slotOptions = availableSlots;

  const handlePhoneChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setCustomerPhone(e.target.value);
  };

  const validateSafaricom = (): string | null => {
    const check = paymentService.formatKenyanPhone(customerPhone);
    if (!check.valid) {
      return 'Please enter a valid Safaricom number in +254 7XX XXX XXX format (e.g. +254712345678).';
    }
    const display = paymentService.formatSafaricomDisplayPhone(customerPhone);
    if (!display.valid) {
      return 'Please enter a valid Safaricom number (07XX, 01XX, 2547XX, +2547XX).';
    }
    return null;
  };

  /** Submit details → create pending booking → initiate STK push for 50% deposit. */
  const handleSubmitBooking = async (e: React.FormEvent) => {
    e.preventDefault();
    setPaymentError(null);

    if (!customerName || !customerPhone) return;
    const phoneErr = validateSafaricom();
    if (phoneErr) {
      setPaymentError(phoneErr);
      return;
    }
    if (!resolvedProvider) {
      setPaymentError('No provider available for the selected services. Please choose another barber.');
      return;
    }
    if (!customerPhone || !selectedDate || !selectedTimeSlot) return;

    try {
      // 1. Create the booking atomically via check_and_reserve (race-condition-safe)
      const desiredStartTs = new Date(
        `${format(selectedDate, 'yyyy-MM-dd')}T${formatSlotDisplay(selectedTimeSlot)}`
      ).toISOString();

      const result = await bookingEngineService.createBooking({
        customerId: '00000000-0000-0000-0000-000000000000', // guest booking
        serviceId: selectedServiceIds[0],
        desiredStartTs,
        preferredStaffIds: [resolvedProvider.id],
        customerName: customerName.trim(),
        customerPhone: paymentService.formatKenyanPhone(customerPhone).formatted,
        customerEmail: customerEmail.trim() || null,
        specialRequests: specialRequests.trim() || null,
        requirePayment: true,
        paymentMethod: 'mpesa'
      });

      if (!result.success) {
        setPaymentStatus('failed');
        setPaymentError(bookingEngineService.mapError(result.error || ''));
        return;
      }

      setPendingBooking({
        bookingId: result.bookingId,
        referenceNumber: result.referenceNumber,
        totalKsh: result.totalPriceKsh,
        depositKsh: result.depositPaidKsh,
        remainingKsh: result.remainingBalanceKsh
      });
      setStep(5);
      setPaymentStatus('pushing');

      // 2. Initiate M-Pesa STK push for exactly the 50% deposit
      const res = await paymentService.initiateMpesaStkPush({
        phoneNumber: paymentService.formatKenyanPhone(customerPhone).formatted,
        amountKsh: depositKsh,
        bookingId: result.bookingId!,
        referenceNumber: result.referenceNumber!,
        customerName: customerName.trim()
      });

      setCheckoutRequestId(res.checkoutRequestId || null);
      setPaymentStatus('awaiting_pin');
    } catch (err: any) {
      setPaymentStatus('failed');
      setPaymentError(err.message || 'Failed to create booking or initiate M-Pesa payment.');
    }
  };

  /** Poll payment status until confirmed. Only then is the booking successful. */
  useEffect(() => {
    if (!isBookingModalOpen || paymentStatus !== 'awaiting_pin' || !checkoutRequestId) return;
    let cancelled = false;

    const poll = async () => {
      try {
        const status = await paymentService.checkPaymentStatus(checkoutRequestId);
        if (cancelled) return;
        if (status.completed) {
          setPaymentStatus('confirmed');
          setPaymentReceipt(status.receiptNumber);
          // Refresh the booking so the reference card shows payment state
          if (pendingBooking?.referenceNumber) {
            const fresh = await bookingService.getBookingByReference(pendingBooking.referenceNumber).catch(() => null);
            if (fresh) setConfirmedBooking({
              ...pendingBooking,
              ...fresh,
              depositPaidKsh: Number(fresh.deposit_paid_ksh || pendingBooking.depositKsh),
              totalPriceKsh: Number(fresh.total_price_ksh || pendingBooking.totalKsh),
              remainingBalanceKsh: Number(fresh.remaining_balance_ksh || pendingBooking.remainingKsh),
              mpesaReceiptNumber: fresh.mpesa_receipt_number || status.receiptNumber,
              paymentStatus: fresh.payment_status,
              customerName,
              serviceNames: selectedServices.map(s => s.name),
              barberName: barberDisplayName,
              timeSlot: formatSlotDisplay(selectedTimeSlot)
            });
          } else {
            setConfirmedBooking({
              ...pendingBooking,
              mpesaReceiptNumber: status.receiptNumber,
              paymentStatus: 'deposit-paid',
              customerName,
              serviceNames: selectedServices.map(s => s.name),
              barberName: barberDisplayName,
              timeSlot: formatSlotDisplay(selectedTimeSlot),
              date: selectedDate ? format(selectedDate, 'yyyy-MM-dd') : ''
            });
          }
          return;
        }
        if (status.status === 'failed') {
          setPaymentStatus('failed');
          setPaymentError('M-Pesa payment was not completed. Please try again.');
          return;
        }
        setTimeout(poll, 3000);
      } catch {
        setTimeout(poll, 3000);
      }
    };

    const timer = setTimeout(poll, 2000);
    return () => {
      cancelled = true;
      clearTimeout(timer);
    };
  }, [paymentStatus, checkoutRequestId, isBookingModalOpen, pendingBooking]);

  const generateGoogleCalendarUrl = () => {
    if (!confirmedBooking) return '#';
    const text = encodeURIComponent(`The Icons Barber & Spa Appointment (${confirmedBooking.serviceNames.join(', ')})`);
    const details = encodeURIComponent(`Master Barber: ${confirmedBooking.barberName}\nReference: ${confirmedBooking.referenceNumber}\nTotal: KSh ${confirmedBooking.totalPriceKsh}\nLocation: ${businessInfo.address.street}, ${businessInfo.address.city}`);
    const location = encodeURIComponent(`${businessInfo.name}, ${businessInfo.address.street}, ${businessInfo.address.city}`);
    return `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${text}&details=${details}&location=${location}`;
  };

  const generateWhatsAppSummaryUrl = () => {
    if (!confirmedBooking) return '#';
    const msg = encodeURIComponent(
      `Hello The Icons Concierge, I have booked an appointment:\n\n*Reference:* ${confirmedBooking.referenceNumber}\n*Services:* ${confirmedBooking.serviceNames.join(', ')}\n*Barber:* ${confirmedBooking.barberName}\n*Date & Time:* ${confirmedBooking.date} at ${confirmedBooking.timeSlot}\n*Client:* ${confirmedBooking.customerName} (${customerPhone})\n*Deposit Paid:* KSh ${confirmedBooking.depositPaidKsh || depositKsh}\n*Total:* KSh ${confirmedBooking.totalPriceKsh}`
    );
    return `https://wa.me/254712345678?text=${msg}`;
  };

  if (!isBookingModalOpen) return null;

  return (
    <div 
      id="booking-modal-backdrop"
      className="fixed inset-0 z-50 bg-background/95 backdrop-blur-md flex items-center justify-center p-3 sm:p-6 overflow-y-auto animate-fadeIn"
      onClick={closeBookingModal}
    >
      <div 
        id="booking-modal-content"
        className="relative w-full max-w-3xl bg-card border border-border-strong rounded-sm shadow-2xl overflow-hidden my-auto max-h-[92vh] flex flex-col"
        onClick={e => e.stopPropagation()}
      >
        {/* Modal Header */}
        <div className="p-4 sm:p-6 bg-card-elevated border-b border-border-subtle flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="w-8 h-8 rounded-sm bg-secondary border border-primary/50 flex items-center justify-center text-primary">
              <Scissors className="w-4 h-4" />
            </div>
            <div>
              <h2 className="text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2">
                Book Your Experience
                {step < 5 && (
                  <span className="text-xs font-mono text-primary font-normal">
                    (Step {step} of 4)
                  </span>
                )}
              </h2>
              <p className="text-[11px] text-muted-foreground">{businessInfo.name} • {businessInfo.address.street}, {businessInfo.address.city}</p>
            </div>
          </div>

          <button
            id="close-booking-modal-btn"
            onClick={closeBookingModal}
            className="w-8 h-8 rounded-sm bg-secondary hover:bg-secondary-hover border border-border-strong flex items-center justify-center text-muted-foreground-light hover:text-white transition-colors"
            aria-label="Close booking modal"
          >
            <X className="w-4 h-4" />
          </button>
        </div>

        {/* Wizard Steps Progress Tracker (Steps 1-4) */}
        {step < 5 && (
          <div className="grid grid-cols-4 bg-background border-b border-border-subtle text-center text-[11px] font-medium">
            {[
              { num: 1, label: '1. Services' },
              { num: 2, label: '2. Barber' },
              { num: 3, label: '3. Schedule' },
              { num: 4, label: '4. Details' }
            ].map(s => (
              <div
                key={s.num}
                className={`py-2.5 px-1 border-r border-border-subtle last:border-r-0 transition-colors ${
                  step === s.num
                    ? 'bg-secondary text-primary font-bold border-b-2 border-b-primary'
                    : step > s.num
                    ? 'text-muted-foreground bg-card'
                    : 'text-muted-foreground/40'
                }`}
              >
                {s.label}
              </div>
            ))}
          </div>
        )}

        {/* Modal Body Container */}
        <div className="p-4 sm:p-6 overflow-y-auto flex-1 space-y-6">

          {/* STEP 1: Select Services */}
          {step === 1 && (
            <div className="space-y-4">
              <div className="flex items-center justify-between">
                <h3 className="text-sm font-bold text-white uppercase tracking-wider">
                  Select Your Treatment(s)
                </h3>
                <span className="text-xs text-muted-foreground">
                  {selectedServiceIds.length} selected
                </span>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[50vh] overflow-y-auto pr-1">
                {services.map(service => {
                  const isSelected = selectedServiceIds.includes(service.id);
                  return (
                    <div
                      key={service.id}
                      onClick={() => toggleService(service.id)}
                      className={`p-3.5 rounded-sm border cursor-pointer transition-all flex items-start justify-between gap-3 ${
                        isSelected 
                          ? 'bg-secondary border-primary shadow-sm' 
                          : 'bg-background border-border hover:border-border-strong'
                      }`}
                    >
                      <div className="space-y-1 flex-1">
                        <div className="flex items-center justify-between">
                          <h4 className="text-xs sm:text-sm font-bold text-white">
                            {service.name}
                          </h4>
                        </div>
                        <p className="text-[11px] text-muted-foreground line-clamp-2 leading-relaxed">
                          {service.shortDescription}
                        </p>
                        <div className="flex items-center gap-3 pt-1 text-[11px]">
                          <span className="text-muted-foreground-light flex items-center gap-1">
                            <Clock className="w-3 h-3 text-primary" /> {service.durationMinutes} min
                          </span>
                          <span className="font-mono font-bold text-primary">
                            KSh {service.priceKsh.toLocaleString()}
                          </span>
                        </div>
                      </div>

                      <div className={`w-5 h-5 rounded-sm border flex items-center justify-center flex-shrink-0 mt-0.5 ${
                        isSelected ? 'bg-primary border-primary text-primary-foreground' : 'border-border-strong'
                      }`}>
                        {isSelected && <Check className="w-3.5 h-3.5" />}
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>
          )}

          {/* STEP 2: Select Barber */}
          {step === 2 && (
            <div className="space-y-4">
              <h3 className="text-sm font-bold text-white uppercase tracking-wider">
                Select Your Master Barber
              </h3>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[50vh] overflow-y-auto pr-1">
                {/* Option: Any Available */}
                <div
                  onClick={() => setSelectedBarberId('any')}
                  className={`p-4 rounded-sm border cursor-pointer transition-all flex items-center gap-3.5 ${
                    selectedBarberId === 'any'
                      ? 'bg-secondary border-primary'
                      : 'bg-background border-border hover:border-border-strong'
                  }`}
                >
                  <div className="w-12 h-12 rounded-sm bg-secondary border border-border-strong flex items-center justify-center text-primary flex-shrink-0">
                    <Sparkles className="w-6 h-6" />
                  </div>
                  <div className="flex-1">
                    <h4 className="text-xs sm:text-sm font-bold text-white">First Available Master</h4>
                    <p className="text-[11px] text-muted-foreground">Any certified master available for optimal speed</p>
                  </div>
                  <div className={`w-5 h-5 rounded-sm border flex items-center justify-center flex-shrink-0 ${
                    selectedBarberId === 'any' ? 'bg-primary border-primary text-primary-foreground' : 'border-border-strong'
                  }`}>
                    {selectedBarberId === 'any' && <Check className="w-3.5 h-3.5" />}
                  </div>
                </div>

                {/* Individual Barbers */}
                {barbers.map(barber => {
                  const isSelected = selectedBarberId === barber.id;
                  const cannotHandle = selectedServiceIds.length > 0 && !canProviderHandle(barber, selectedServiceIds);
                  return (
                    <div
                      key={barber.id}
                      onClick={() => {
                        if (!cannotHandle) setSelectedBarberId(barber.id);
                      }}
                      className={`p-3.5 rounded-sm border transition-all flex items-center gap-3.5 ${
                        cannotHandle
                          ? 'opacity-40 cursor-not-allowed bg-background border-border'
                          : isSelected
                          ? 'cursor-pointer bg-secondary border-primary'
                          : 'cursor-pointer bg-background border-border hover:border-border-strong'
                      }`}
                    >
                      <img
                        src={barber.avatarUrl}
                        alt={barber.name}
                        className="w-12 h-12 rounded-sm object-cover border border-border-strong flex-shrink-0"
                        onError={e => {
                          (e.currentTarget as HTMLImageElement).src = 'https://images.unsplash.com/photo-1599351431613-18ef1fdd27e1?w=500&auto=format&fit=crop&q=60';
                        }}
                      />
                      <div className="flex-1 min-w-0">
                        <h4 className="text-xs sm:text-sm font-bold text-white truncate">{barber.name}</h4>
                        <p className="text-[10px] text-primary uppercase tracking-wider font-semibold truncate">{barber.title}</p>
                        <p className="text-[11px] text-muted-foreground truncate">{barber.specialty}</p>
                        {cannotHandle && (
                          <p className="text-[10px] text-destructive mt-0.5">Cannot perform all selected services</p>
                        )}
                      </div>
                      <div className={`w-5 h-5 rounded-sm border flex items-center justify-center flex-shrink-0 ${
                        isSelected ? 'bg-primary border-primary text-primary-foreground' : 'border-border-strong'
                      }`}>
                        {isSelected && <Check className="w-3.5 h-3.5" />}
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>
          )}

          {/* STEP 3: Select Date & Time Slot */}
          {step === 3 && (
            <div className="space-y-6">
              {/* Date Selection — react-day-picker calendar */}
              <div>
                <h3 className="text-sm font-bold text-white uppercase tracking-wider mb-3">
                  1. Select Date
                </h3>
                <div className="bg-background border border-border rounded-sm p-3 flex justify-center">
                  <DayPicker
                    mode="single"
                    selected={selectedDate}
                    onSelect={(date) => {
                      setSelectedDate(date);
                      setSelectedTimeSlot('');
                    }}
                    disabled={{
                      before: startOfDay(addDays(new Date(), 1)),
                      after: addDays(new Date(), 45)
                    }}
                    className="!font-sans"
                  />
                </div>
                {selectedDate && (
                  <p className="text-[11px] text-muted-foreground mt-2">
                    Selected: <span className="text-primary font-mono font-semibold">{format(selectedDate, 'EEEE, MMMM d, yyyy')}</span>
                  </p>
                )}
              </div>

              {/* Time Slots */}
              <div>
                <div className="flex items-center justify-between mb-3">
                  <h3 className="text-sm font-bold text-white uppercase tracking-wider">
                    2. Select Time Slot
                  </h3>
                  {slotsLoading && (
                    <span className="text-[10px] text-muted-foreground flex items-center gap-1">
                      <Loader2 className="w-3 h-3 animate-spin" /> Checking availability...
                    </span>
                  )}
                </div>

                {!resolvedProvider ? (
                  <p className="text-xs text-destructive p-3 bg-destructive/10 border border-destructive/30 rounded-sm">
                    No provider is available for the selected services. Go back and pick another barber.
                  </p>
                ) : (
                  <div className="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2">
                    {slotOptions.map(slot => {
                      const display = formatSlotDisplay(slot.start_time);
                      const endDisplay = slot.end_time ? formatSlotDisplay(slot.end_time) : '';
                      const rangeDisplay = endDisplay ? `${display}–${endDisplay}` : display;
                      const booked = isSlotBooked(slot.start_time, slot.end_time);
                      const isSelected = selectedTimeSlot === slot.start_time;
                      return (
                        <button
                          key={slot.start_time}
                          type="button"
                          disabled={booked}
                          onClick={() => setSelectedTimeSlot(slot.start_time)}
                          title={booked ? 'Already booked — choose another time' : `${rangeDisplay} (${totalDuration} min)`}
                          className={`px-2 py-1.5 rounded-sm border text-xs font-mono transition-all relative ${
                            booked
                              ? 'bg-background border-border text-muted-foreground/40 line-through cursor-not-allowed'
                              : isSelected
                              ? 'bg-primary text-primary-foreground border-primary font-bold'
                              : 'bg-background text-muted-foreground-light border-border hover:text-white hover:bg-secondary'
                          }`}
                        >
                          <span className="block leading-tight">{display}</span>
                          {endDisplay && (
                            <span className={`block text-[9px] leading-tight ${isSelected ? 'text-primary-foreground/80' : 'text-muted-foreground'}`}>
                              {endDisplay}
                            </span>
                          )}
                          {booked && (
                            <span className="absolute -top-1.5 -right-1.5 bg-destructive text-[8px] text-white px-1 rounded-sm font-sans normal-case no-underline">
                              Booked
                            </span>
                          )}
                        </button>
                      );
                    })}
                  </div>
                )}

                {bookedSlots.length > 0 && !slotsLoading && (
                  <p className="text-[10px] text-muted-foreground mt-2">
                    <span className="inline-block w-2 h-2 bg-destructive rounded-sm mr-1 align-middle" />
                    Slots marked Booked are already reserved by other clients.
                  </p>
                )}

                {!slotsLoading && slotOptions.length === 0 && (
                  <div className="p-3 bg-secondary/50 border border-border rounded-sm space-y-2">
                    <p className="text-xs text-muted-foreground">
                      No available {totalDuration}-minute slots for {barberDisplayName} on {selectedDate ? format(selectedDate, 'yyyy-MM-dd') : 'this date'}. Please try another date or provider.
                    </p>
                    {qualifiedStaff.length > 0 && (
                      <p className="text-[11px] text-muted-foreground">
                        <span className="text-primary font-semibold">Masters qualified for this service:</span>{' '}
                        {qualifiedStaff.map(q => q.staffName).join(', ')}
                        {' — '}try another date or select one of them specifically.
                      </p>
                    )}
                  </div>
                )}
              </div>
            </div>
          )}

          {/* STEP 4: Client Contact Details */}
          {step === 4 && (
            <form onSubmit={handleSubmitBooking} className="space-y-4">
              <h3 className="text-sm font-bold text-white uppercase tracking-wider">
                Enter Your Contact Information
              </h3>

              <div className="space-y-3 text-xs">
                <div>
                  <label className="block text-foreground/90 font-semibold mb-1">
                    Full Name <span className="text-primary">*</span>
                  </label>
                  <Input
                    type="text"
                    required
                    placeholder="e.g. Kiprono Tanui"
                    value={customerName}
                    onChange={e => setCustomerName(e.target.value)}
                    className="py-2.5 rounded-sm"
                    icon={<User className="w-4 h-4" />}
                  />
                </div>

                <div>
                  <label className="block text-foreground/90 font-semibold mb-1">
                    Phone Number (M-Pesa / SMS, Safaricom) <span className="text-primary">*</span>
                  </label>
                  <Input
                    type="tel"
                    required
                    placeholder="+254 7XX XXX XXX"
                    value={customerPhone}
                    onChange={handlePhoneChange}
                    className="py-2.5 rounded-sm font-mono"
                    icon={<Phone className="w-4 h-4" />}
                  />
                  <p className="text-[10px] text-muted-foreground mt-1">
                    We send the M-Pesa deposit prompt to this Safaricom number (+254 format).
                  </p>
                </div>

                <div>
                  <label className="block text-foreground/90 font-semibold mb-1">
                    Email Address (Optional for digital calendar invite)
                  </label>
                  <Input
                    type="email"
                    placeholder="e.g. name@company.co.ke"
                    value={customerEmail}
                    onChange={e => setCustomerEmail(e.target.value)}
                    className="py-2.5 rounded-sm"
                    icon={<Mail className="w-4 h-4" />}
                  />
                </div>

                <div>
                  <label className="block text-foreground/90 font-semibold mb-1">
                    Special Requests or Styling Notes (Optional)
                  </label>
                  <textarea
                    rows={2}
                    placeholder="e.g. Prefer low skin taper, warm towel eucalyptus aroma..."
                    value={specialRequests}
                    onChange={e => setSpecialRequests(e.target.value)}
                    className="input-base input-default w-full p-2.5 rounded-sm"
                  />
                </div>
              </div>

              {paymentError && (
                <div className="flex items-center gap-2 p-2.5 bg-destructive/10 border border-destructive/30 rounded-sm text-xs text-destructive">
                  <AlertCircle className="w-4 h-4 shrink-0" />
                  <span>{paymentError}</span>
                </div>
              )}

              {/* Order Summary Box with 50% deposit */}
              <div className="p-4 bg-secondary border border-border rounded-sm space-y-2 text-xs">
                <div className="flex justify-between text-muted-foreground">
                  <span>Services ({selectedServices.length}):</span>
                  <span className="text-white">{selectedServices.map(s => s.name).join(', ')}</span>
                </div>
                <div className="flex justify-between text-muted-foreground">
                  <span>Master Barber:</span>
                  <span className="text-primary font-semibold">{barberDisplayName}</span>
                </div>
                <div className="flex justify-between text-muted-foreground">
                  <span>Date & Time:</span>
                  <span className="font-mono text-white">{selectedDate ? format(selectedDate, 'yyyy-MM-dd') : ''} at {formatSlotDisplay(selectedTimeSlot || '')}</span>
                </div>
                <div className="flex justify-between text-muted-foreground">
                  <span>Total Duration:</span>
                  <span className="text-white">{totalDuration} min</span>
                </div>
                <div className="pt-2 border-t border-border flex justify-between items-center text-sm font-bold">
                  <span className="text-white">Total Experience:</span>
                  <span className="font-mono text-primary text-base">KSh {totalPrice.toLocaleString()}</span>
                </div>
                <div className="flex justify-between items-center text-[11px]">
                  <span className="text-muted-foreground flex items-center gap-1">
                    <Lock className="w-3 h-3 text-primary" /> 50% Deposit via M-Pesa (secures slot)
                  </span>
                  <span className="font-mono font-bold text-white">KSh {depositKsh.toLocaleString()}</span>
                </div>
                <div className="flex justify-between items-center text-[11px] text-muted-foreground">
                  <span>Balance due at chair</span>
                  <span className="font-mono font-bold text-white">KSh {depositBreakdown.remainingKsh.toLocaleString()}</span>
                </div>
              </div>

              <div className="pt-2">
                <Button
                  type="submit"
                  variant="primary"
                  size="lg"
                  className="w-full uppercase tracking-wider text-xs shadow-xl"
                >
                  <Smartphone className="w-4 h-4" />
                  <span>Confirm & Pay 50% Deposit (M-Pesa)</span>
                </Button>
                <p className="text-[10px] text-muted-foreground text-center mt-2">
                  Your slot is held as pending until the M-Pesa deposit is confirmed. No login required.
                </p>
              </div>
            </form>
          )}

          {/* STEP 5: Payment / Booking Confirmation */}
          {step === 5 && (
            <div className="text-center py-6 space-y-6 animate-fadeIn">
              {paymentStatus === 'pushing' || paymentStatus === 'awaiting_pin' ? (
                <>
                  <div className="w-16 h-16 rounded-full bg-secondary border-2 border-primary text-primary flex items-center justify-center mx-auto shadow-lg">
                    <Loader2 className="w-8 h-8 animate-spin" />
                  </div>
                  <div className="space-y-2">
                    <span className="text-xs uppercase tracking-[0.2em] text-primary font-semibold">
                      {paymentStatus === 'pushing' ? 'Reserving Your Slot' : 'Awaiting M-Pesa Confirmation'}
                    </span>
                    <h3 className="text-xl sm:text-2xl font-bold text-white">
                      {paymentStatus === 'pushing' ? 'Creating Your Reservation' : 'Enter your M-Pesa PIN to confirm'}
                    </h3>
                    <p className="text-xs sm:text-sm text-muted-foreground-light max-w-md mx-auto">
                      {paymentStatus === 'pushing'
                        ? 'We are securely reserving your preferred slot and sending the deposit prompt.'
                        : `A prompt for KSh ${depositKsh.toLocaleString()} has been sent to +${paymentService.formatKenyanPhone(customerPhone).formatted.replace(/^\+?/, '')}. Your booking is confirmed automatically once the payment clears.`}
                    </p>
                  </div>
                  {pendingBooking && (
                    <div className="max-w-md mx-auto bg-secondary border border-border-strong p-5 rounded-sm text-left space-y-3 text-xs">
                      <div className="flex justify-between items-center pb-2 border-b border-border">
                        <span className="text-muted-foreground uppercase tracking-wider text-[10px]">Booking Reference</span>
                        <span className="font-mono font-bold text-primary text-sm">{pendingBooking.referenceNumber}</span>
                      </div>
                      <div className="space-y-1.5 text-foreground/90">
                        <p><strong className="text-white">Provider:</strong> {barberDisplayName}</p>
                        <p><strong className="text-white">Date & Time:</strong> {selectedDate ? format(selectedDate, 'yyyy-MM-dd') : ''} @ {formatSlotDisplay(selectedTimeSlot || '')}</p>
                        <p><strong className="text-white">Total:</strong> KSh {pendingBooking.totalKsh?.toLocaleString?.() || totalPrice.toLocaleString()}</p>
                        <p><strong className="text-white">Deposit Required:</strong> KSh {depositKsh.toLocaleString()}</p>
                      </div>
                    </div>
                  )}
                  <p className="text-[10px] text-muted-foreground max-w-sm mx-auto">
                    Please do not close this window. We will update automatically once payment is confirmed (typically under 30 seconds).
                  </p>
                </>
              ) : paymentStatus === 'confirmed' && confirmedBooking ? (
                <>
                  <div className="w-16 h-16 rounded-full bg-secondary border-2 border-primary text-primary flex items-center justify-center mx-auto shadow-lg">
                    <CheckCircle2 className="w-8 h-8" />
                  </div>

                  <div className="space-y-2">
                    <span className="text-xs uppercase tracking-[0.2em] text-primary font-semibold">
                      Deposit Confirmed — Booking Secured
                    </span>
                    <h3 className="text-2xl font-bold text-white">
                      We Look Forward to Welcoming You
                    </h3>
                    <p className="text-xs sm:text-sm text-muted-foreground-light max-w-md mx-auto">
                      Your 50% deposit has been received and your chair is reserved at {businessInfo.name}, {businessInfo.address.street}, {businessInfo.address.city}.
                    </p>
                  </div>

                  {/* Reference Card */}
                  <div className="max-w-md mx-auto bg-secondary border border-border-strong p-5 rounded-sm text-left space-y-3 text-xs">
                    <div className="flex justify-between items-center pb-2 border-b border-border">
                      <span className="text-muted-foreground uppercase tracking-wider text-[10px]">Booking Reference</span>
                      <span className="font-mono font-bold text-primary text-sm">
                        {confirmedBooking.referenceNumber}
                      </span>
                    </div>

                    <div className="space-y-1.5 text-foreground/90">
                      <p><strong className="text-white">Client:</strong> {confirmedBooking.customerName}</p>
                      <p><strong className="text-white">Barber:</strong> {confirmedBooking.barberName}</p>
                      <p><strong className="text-white">Date & Time:</strong> {confirmedBooking.date} @ {confirmedBooking.timeSlot}</p>
                      <p><strong className="text-white">Services:</strong> {confirmedBooking.serviceNames.join(', ')}</p>
                      <p><strong className="text-white">Deposit Paid (M-Pesa):</strong> KSh {confirmedBooking.depositPaidKsh?.toLocaleString?.() || depositKsh.toLocaleString()} {confirmedBooking.mpesaReceiptNumber && <span className="text-primary font-mono">• {confirmedBooking.mpesaReceiptNumber}</span>}</p>
                      <p><strong className="text-white">Total:</strong> KSh {confirmedBooking.totalPriceKsh?.toLocaleString?.() || totalPrice.toLocaleString()}</p>
                      <p className="text-muted-foreground"><strong className="text-white">Balance at Chair:</strong> KSh {confirmedBooking.remainingBalanceKsh?.toLocaleString?.() || depositBreakdown.remainingKsh.toLocaleString()}</p>
                    </div>
                  </div>

                  {/* Action buttons */}
                  <div className="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2 max-w-md mx-auto">
                    <a
                      href={generateGoogleCalendarUrl()}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="w-full sm:w-1/2 py-2.5 px-3 bg-secondary hover:bg-secondary-hover text-white border border-border-strong text-xs font-semibold rounded-sm transition-colors flex items-center justify-center gap-1.5"
                    >
                      <CalendarIcon className="w-3.5 h-3.5 text-primary" />
                      <span>Add to Calendar</span>
                    </a>

                    <a
                      href={generateWhatsAppSummaryUrl()}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="w-full sm:w-1/2 py-2.5 px-3 bg-secondary hover:bg-secondary-hover text-success border border-success/40 text-xs font-semibold rounded-sm transition-colors flex items-center justify-center gap-1.5"
                    >
                      <MessageSquare className="w-3.5 h-3.5" />
                      <span>Save on WhatsApp</span>
                    </a>
                  </div>

                  <div className="pt-4">
                    <button
                      onClick={closeBookingModal}
                      className="text-xs text-muted-foreground hover:text-white underline cursor-pointer"
                    >
                      Return to Website
                    </button>
                  </div>
                </>
              ) : paymentStatus === 'failed' ? (
                <>
                  <div className="w-16 h-16 rounded-full bg-destructive/10 border-2 border-destructive text-destructive flex items-center justify-center mx-auto">
                    <AlertCircle className="w-8 h-8" />
                  </div>
                  <div className="space-y-2">
                    <span className="text-xs uppercase tracking-[0.2em] text-destructive font-semibold">
                      Payment Not Completed
                    </span>
                    <h3 className="text-xl font-bold text-white">
                      Your booking is not yet confirmed
                    </h3>
                    <p className="text-xs sm:text-sm text-muted-foreground-light max-w-md mx-auto">
                      {paymentError || 'The M-Pesa payment did not complete, so your slot has not been secured. You can retry without losing your selection.'}
                    </p>
                  </div>
                  <div className="flex flex-col sm:flex-row gap-3 justify-center max-w-md mx-auto">
                    <Button
                      variant="primary"
                      onClick={() => { setPaymentStatus('pushing'); setPaymentError(null); handleSubmitBooking(new Event('submit') as any); }}
                      className="w-full sm:w-auto"
                    >
                      <Smartphone className="w-4 h-4 mr-1" />
                      Retry Payment
                    </Button>
                    <Button
                      variant="outline"
                      onClick={closeBookingModal}
                      className="w-full sm:w-auto"
                    >
                      Close
                    </Button>
                  </div>
                </>
              ) : null}
            </div>
          )}

        </div>

        {/* Modal Footer Controls (Steps 1-3) */}
        {step < 4 && (
          <div className="p-4 sm:p-5 bg-card-elevated border-t border-border-subtle flex items-center justify-between">
            {step > 1 ? (
              <Button
                type="button"
                variant="secondary"
                size="sm"
                onClick={() => setStep((step - 1) as any)}
                className="text-xs gap-1"
              >
                <ChevronLeft className="w-4 h-4" />
                <span>Previous</span>
              </Button>
            ) : (
              <div className="text-xs text-muted-foreground">
                Total: <span className="text-primary font-mono font-bold">KSh {totalPrice.toLocaleString()}</span>
              </div>
            )}

            <Button
              type="button"
              variant="primary"
              size="md"
              onClick={() => setStep((step + 1) as any)}
              className="text-xs uppercase font-bold tracking-wider gap-1.5"
            >
              <span>Continue</span>
              <ChevronRight className="w-4 h-4" />
            </Button>
          </div>
        )}

      </div>
    </div>
  );
};