import React, { useState, useRef } from 'react';
import { Upload, X, CheckCircle2, AlertCircle, Loader2, Image as ImageIcon } from 'lucide-react';
import { storageService } from '../../../services/storageService';
import { Button } from '../../ui/Button';

interface ImageUploaderProps {
  currentImageUrl?: string;
  onImageUploaded: (url: string) => void;
  onImageRemoved?: () => void;
  bucket?: 'avatars' | 'services' | 'business';
  aspectRatio?: 'square' | 'wide' | 'banner';
  label?: string;
  helperText?: string;
  disabled?: boolean;
}

export const ImageUploader: React.FC<ImageUploaderProps> = ({
  currentImageUrl,
  onImageUploaded,
  onImageRemoved,
  bucket = 'avatars',
  aspectRatio = 'square',
  label = 'Upload Image',
  helperText = 'JPG, PNG, or WEBP. Max 5MB.',
  disabled = false
}) => {
  const [previewUrl, setPreviewUrl] = useState<string | undefined>(currentImageUrl);
  const [isUploading, setIsUploading] = useState<boolean>(false);
  const [uploadError, setUploadError] = useState<string | null>(null);
  const [isDragOver, setIsDragOver] = useState<boolean>(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  // Sync if external currentImageUrl changes
  React.useEffect(() => {
    setPreviewUrl(currentImageUrl);
  }, [currentImageUrl]);

  const handleFile = async (file: File) => {
    setUploadError(null);

    const validation = storageService.validateImage(file);
    if (!validation.valid) {
      setUploadError(validation.error || 'Invalid image file');
      return;
    }

    try {
      setIsUploading(true);
      const result = await storageService.uploadImage(file, bucket as any);
      setPreviewUrl(result.url);
      onImageUploaded(result.url);
    } catch (err: any) {
      setUploadError(err.message || 'Image upload failed. Please try again.');
    } finally {
      setIsUploading(false);
    }
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      handleFile(file);
    }
  };

  const handleDrop = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    setIsDragOver(false);
    if (disabled || isUploading) return;

    const file = e.dataTransfer.files?.[0];
    if (file) {
      handleFile(file);
    }
  };

  const handleRemove = (e: React.MouseEvent) => {
    e.stopPropagation();
    setPreviewUrl(undefined);
    setUploadError(null);
    if (fileInputRef.current) fileInputRef.current.value = '';
    if (onImageRemoved) onImageRemoved();
    else onImageUploaded('');
  };

  const getAspectClass = () => {
    switch (aspectRatio) {
      case 'banner':
        return 'aspect-[21/9] w-full';
      case 'wide':
        return 'aspect-[16/9] w-full';
      case 'square':
      default:
        return 'aspect-square w-32 sm:w-40';
    }
  };

  return (
    <div className="space-y-2">
      {label && (
        <label className="block text-xs font-bold uppercase tracking-wider text-muted-foreground">
          {label}
        </label>
      )}

      <div
        onDragOver={(e) => { e.preventDefault(); if (!disabled) setIsDragOver(true); }}
        onDragLeave={() => setIsDragOver(false)}
        onDrop={handleDrop}
        onClick={() => !disabled && !isUploading && fileInputRef.current?.click()}
        className={`relative group rounded-xl border border-dashed transition-all duration-200 overflow-hidden cursor-pointer flex flex-col items-center justify-center p-3 text-center ${
          getAspectClass()
        } ${
          isDragOver 
            ? 'border-primary bg-primary/10' 
            : previewUrl 
            ? 'border-border bg-card' 
            : 'border-border hover:border-primary/50 bg-card/60 hover:bg-card'
        } ${disabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''}`}
      >
        <input
          ref={fileInputRef}
          type="file"
          accept="image/jpeg,image/png,image/webp,image/gif"
          onChange={handleInputChange}
          className="hidden"
          disabled={disabled || isUploading}
        />

        {/* Uploading Spinner */}
        {isUploading && (
          <div className="absolute inset-0 bg-background/80 backdrop-blur-xs flex flex-col items-center justify-center z-20 space-y-2">
            <Loader2 className="w-6 h-6 animate-spin text-primary" />
            <span className="text-[11px] font-semibold text-foreground">Processing Image...</span>
          </div>
        )}

        {/* Image Preview */}
        {previewUrl ? (
          <div className="relative w-full h-full">
            <img
              src={previewUrl}
              alt="Preview"
              className="w-full h-full object-cover rounded-lg"
              referrerPolicy="no-referrer"
            />
            {/* Hover overlay with action buttons */}
            <div className="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-2 p-2 z-10">
              <Button
                type="button"
                variant="primary"
                size="sm"
                className="text-[11px] py-1 px-2.5 h-auto"
                onClick={(e) => {
                  e.stopPropagation();
                  fileInputRef.current?.click();
                }}
              >
                Replace
              </Button>
              <Button
                type="button"
                variant="destructive"
                size="sm"
                className="text-[11px] py-1 px-2.5 h-auto"
                onClick={handleRemove}
              >
                <X className="w-3.5 h-3.5 mr-1" />
                Remove
              </Button>
            </div>
          </div>
        ) : (
          /* Empty / Upload Prompt State */
          <div className="flex flex-col items-center justify-center space-y-2 p-4">
            <div className="w-10 h-10 rounded-full bg-muted flex items-center justify-center text-muted-foreground group-hover:text-primary transition-colors">
              <Upload className="w-5 h-5" />
            </div>
            <div className="space-y-0.5">
              <p className="text-xs font-semibold text-foreground">
                <span className="text-primary font-bold">Click to upload</span> or drag and drop
              </p>
              <p className="text-[10px] text-muted-foreground">{helperText}</p>
            </div>
          </div>
        )}
      </div>

      {/* Error Message */}
      {uploadError && (
        <div className="flex items-center gap-1.5 text-xs text-destructive mt-1">
          <AlertCircle className="w-3.5 h-3.5 shrink-0" />
          <span>{uploadError}</span>
        </div>
      )}
    </div>
  );
};
