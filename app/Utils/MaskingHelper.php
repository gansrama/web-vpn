<?php

namespace App\Utils;

class MaskingHelper
{
    /**
     * Mask email address - shows first 2 characters and domain
     * Example: john.doe@example.com -> jo***@example.com
     */
    public static function maskEmail(string $email): string
    {
        if (empty($email)) {
            return '';
        }

        $atIndex = strpos($email, '@');
        if ($atIndex === false || $atIndex <= 0) {
            return $email;
        }

        $username = substr($email, 0, $atIndex);
        $domain = substr($email, $atIndex);

        if (strlen($username) <= 2) {
            return $username[0] . '***' . $domain;
        }

        return substr($username, 0, 2) . '***' . $domain;
    }

    /**
     * Mask phone number - shows first 3 and last 3 digits
     * Example: 08123456789 -> 081****789
     */
    public static function maskPhone(string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Remove non-numeric characters first
        $cleanPhone = preg_replace('/\D/', '', $phone);

        if (strlen($cleanPhone) <= 6) {
            return substr($cleanPhone, 0, 2) . '***';
        }

        $start = substr($cleanPhone, 0, 3);
        $end = substr($cleanPhone, -3);
        $middle = str_repeat('*', strlen($cleanPhone) - 6);

        return $start . $middle . $end;
    }

    /**
     * Mask NIK (Nomor Induk Kependudukan) - shows first 4 and last 4 digits
     * Example: 1234567890123456 -> 1234********3456
     */
    public static function maskNIK(string $nik): string
    {
        if (empty($nik)) {
            return '';
        }

        // Remove non-numeric characters first
        $cleanNIK = preg_replace('/\D/', '', $nik);

        if (strlen($cleanNIK) <= 8) {
            return substr($cleanNIK, 0, 2) . '***';
        }

        $start = substr($cleanNIK, 0, 4);
        $end = substr($cleanNIK, -4);
        $middle = str_repeat('*', strlen($cleanNIK) - 8);

        return $start . $middle . $end;
    }

    /**
     * Mask employee data for API response
     * Returns a new array with masked sensitive fields
     */
    public static function maskEmployeeData($employee): array
    {
        if (is_object($employee)) {
            $employee = $employee->toArray();
        }

        return [
            'id' => $employee['id'] ?? null,
            'nama_lengkap' => $employee['nama_lengkap'] ?? '',
            'nomor_ktp' => self::maskNIK($employee['nomor_ktp'] ?? ''),
            'email' => self::maskEmail($employee['email'] ?? ''),
            'username_vpn' => $employee['username_vpn'] ?? '',
            'posisi_jabatan' => $employee['posisi_jabatan'] ?? '',
            'nama_organisasi' => $employee['nama_organisasi'] ?? '',
            'nomer_hp_wa' => self::maskPhone($employee['nomer_hp_wa'] ?? ''),
            'created_at' => $employee['created_at'] ?? null,
            'updated_at' => $employee['updated_at'] ?? null,
        ];
    }
}
