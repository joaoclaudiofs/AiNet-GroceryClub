<?php
namespace App\Traits;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait UserPhotoFileStorage
{
    public function storeUserPhoto(?UploadedFile $uploadedFile, User $user): ?string
    {
        if ($uploadedFile) {
            $path = basename(Storage::disk('public')->putFile('users', $uploadedFile));
            $user->photo = $path;
            $user->save();
            return $path;
        }
        return null;
    }

    public function deleteUserPhoto(User $user): bool
    {
        if ($user->photo) {
            if (Storage::disk('public')->exists('users/' . $user->photo)) {
                Storage::disk('public')->delete('users/' . $user->photo);
                $user->photo = null;
                $user->save();
                return true;
            }
            $user->photo = null;
            $user->save();
        }
        return false;
    }

    public function deletePhotoFile(?string $photo): bool
    {
        if ($photo !== null) {
            if (Storage::disk('public')->exists('users/' . $photo)) {
                Storage::disk('public')->delete('users/' . $photo);
                return true;
            }
        }
        return false;
    }
}