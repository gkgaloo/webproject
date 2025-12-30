# Candidate Photo Upload Feature - Setup Instructions

## Quick Setup Guide

### 1. Database Migration

Run the SQL migration to add photo support to the candidates table:

```sql
-- Option 1: Using phpMyAdmin
1. Open http://localhost/phpmyadmin
2. Select 'voting_system' database
3. Go to 'Import' tab
4. Choose file: C:\xampp\htdocs\onlinevoting\backend\photo_upload_migration.sql
5. Click 'Go'

-- Option 2: Execute directly
USE voting_system;
ALTER TABLE candidates ADD COLUMN photo VARCHAR(255) NULL AFTER image;
```

### 2. Verify Upload Directory

The upload directory has been created at:
- `C:\xampp\htdocs\onlinevoting\uploads\candidates\`

Verify permissions are correct (should be writable by Apache/PHP):
```powershell
# Check if directory exists
Test-Path "C:\xampp\htdocs\onlinevoting\uploads\candidates\"
```

### 3. Test Photo Upload

1. **Login as Admin**:
   - Go to: http://localhost/onlinevoting/admin-login.html
   - Email: admin@voting.com
   - Password: admin123

2. **Add Candidate with Photo**:
   - Navigate to Admin Dashboard
   - Scroll to "Add New Candidate" form
   - Fill in candidate details
   - Click "Choose File" for photo upload
   - Select an image (JPG, JPEG, or PNG, max 2MB)
   - Preview will appear automatically
   - Click "Add Candidate"

3. **Verify Upload**:
   - Check `C:\xampp\htdocs\onlinevoting\uploads\candidates\` directory
   - You should see uploaded file (e.g., `candidate_1_1234567890.jpg`)
   - Photo should display in candidate table

4. **Test Voter View**:
   - Login as a voter
   - Navigate to Voter Dashboard
   - Candidate photos should display as 120px circular images

5. **Test Results Page**:
   - Go to: http://localhost/onlinevoting/results.html
   - Candidate photos should display alongside results

## Features Implemented

### Admin Side
- ✅ Photo upload field in "Add Candidate" form
- ✅ Client-side file validation (type, size)
- ✅ Image preview before upload
- ✅ Photo display in candidate management table
- ✅ Photo edit support (upload new photo when editing)
- ✅ Automatic old photo deletion when updated

### Voter Side
- ✅ Candidate photos displayed in voting dashboard (120px circular)
- ✅ Photos shown on results page (60px circular)
- ✅ Default placeholder if no photo uploaded
- ✅ Error handling with fallback to default image

### Backend & Security
- ✅ Server-side validation (file type, size, mime type)
- ✅ Unique filename generation (prevents conflicts)
- ✅ Prepared statements for SQL injection prevention
- ✅ Photo deletion on candidate removal
- ✅ Image optimization and resizing support
- ✅ Secure file storage in dedicated directory

## File Upload Specifications

- **Allowed Types**: JPG, JPEG, PNG
- **Max File Size**: 2MB
- **Upload Directory**: `/uploads/candidates/`
- **Filename Format**: `candidate_{id}_{timestamp}.{extension}`
- **Default Placeholder**: `/uploads/candidates/default_candidate.png`

## API Endpoints Updated

### Add Candidate with Photo
```
POST /backend/admin/add_candidate.php
Content-Type: multipart/form-data

Fields:
- name: string (required)
- party: string (required)
- description: string
- image: string (emoji icon)
- photo: file (image file)
```

### Edit Candidate with Photo
```
POST /backend/admin/edit_candidate.php
Content-Type: multipart/form-data

Fields:
- id: integer (required)
- name: string (required)
- party: string (required)
- description: string
- image: string (emoji icon)
- photo: file (image file, optional)
```

### Get Candidates (includes photo URLs)
```
GET /backend/voter/get_candidates.php

Response includes:
- photo: relative path (string or null)
- photo_url: full URL to display image
```

## Troubleshooting

### Photo Not Uploading
1. Check PHP upload settings in `php.ini`:
   - `upload_max_filesize = 10M`
   - `post_max_size = 10M`
2. Verify directory is writable
3. Check browser console for errors
4. Review Apache error logs: `C:\xampp\apache\logs\error.log`

### Photo Not Displaying
1. Verify file exists in `uploads/candidates/` directory
2. Check file permissions (should be readable)
3. Verify URL path in browser DevTools
4. Ensure `.htaccess` allows access to candidate images

### Default Placeholder Not Shown
1. Verify `default_candidate.png` exists in `uploads/candidates/`
2. Check browser console for 404 errors
3. Try accessing directly: `http://localhost/onlinevoting/uploads/candidates/default_candidate.png`

## Database Schema Changes

```sql
-- Added to candidates table:
photo VARCHAR(255) NULL -- Stores relative path like 'uploads/candidates/candidate_1_1234567890.jpg'
```

## Files Modified/Created

### New Files
- `backend/photo_upload_migration.sql` - Database migration
- `backend/includes/upload.php` - Photo upload utilities
- `js/photo-upload.js` - Frontend photo handling
- `uploads/candidates/default_candidate.png` - Default placeholder

### Modified Files
- `backend/admin/add_candidate.php` - Added photo upload support
- `backend/admin/edit_candidate.php` - Added photo edit support
- `backend/admin/results.php` - Added photo_url to results
- `backend/voter/get_candidates.php` - Added photo_url to candidates
- `admin-dashboard.html` - Added photo upload form
- `voter-dashboard.html` - Display candidate photos
- `results.html` - Display photos in results

## Next Steps

1. Test all functionality end-to-end
2. Consider adding image cropping/resizing on upload
3. Implement photo gallery for candidate profiles
4. Add photo compression for better performance
5. Consider CDN for production deployment

## Support

For issues or questions:
- Check error logs in `C:\xampp\apache\logs\error.log`
- Verify all files are in correct locations
- Ensure database migration completed successfully
- Test with different image formats and sizes
