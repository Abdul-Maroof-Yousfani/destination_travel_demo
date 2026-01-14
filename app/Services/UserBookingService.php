<?php
namespace App\Services;

use Carbon\Carbon;
use App\Mail\SendMail;
use App\Models\Client;
use App\Models\BookingId;
use App\Models\Passenger;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UserBookingService
{
    public function createUser($userData)
    {
        try {
            $password = Str::random(8);

            $title  = ucwords(strtoupper($userData['title'] ?? 'MR'));
            $name   = $userData['userFullName'] ?? '-';
            $email  = $userData['userEmail'] ?? null;
            $phoneCode = $userData['userPhoneCode'] ?? '-';
            $phone = $userData['userPhone'] ?? '-';
            $country_code = $userData['countryCode'] ?? '-';
            $country_name = $userData['country'] ?? '-';
            $city_code = $userData['cityCode'] ?? '-';
            $city = $userData['city'] ?? '-';
            $acceptOffers = isset($userData['acceptOffers']) ? (bool) $userData['acceptOffers'] : false;

            if (!$email) {
                throw new \Exception("Email is required to create or update user.");
            }

            $user = Client::where('email', $email)->first();

            if ($user) {
                $user->update([
                    'title' => $title,
                    'name' => $name,
                    'phone_code' => $phoneCode,
                    'phone' => $phone,
                    'accept_notification' => $acceptOffers,
                    'ip' => request()->ip(),
                    'country_code' => $country_code,
                    'country_name' => $country_name,
                    'city_code' => $city_code,
                    'city' => $city,
                ]);
            } else {
                $user = Client::create([
                    'title' => $title,
                    'name' => $name,
                    'email' => $email,
                    'phone_code' => $phoneCode,
                    'phone' => $phone,
                    'accept_notification' => $acceptOffers,
                    'password' => Hash::make($password),
                    'original_password' => $password,
                    'ip' => request()->ip(),
                    'country_code' => $country_code,
                    'country_name' => $country_name,
                    'city_code' => $city_code,
                    'city' => $city,
                ]);
            }

            return $user;
        } catch (\Exception $e) {
            \Log::error('User creation failed: ' . $e->getMessage());
            return null;
        }
    }
    public function createPassengers(array $passengers, int $clientId)
    {
        return DB::transaction(function () use ($passengers, $clientId) {
            $rows = Arr::isAssoc($passengers) ? [$passengers] : $passengers;

            return collect($rows)->map(function ($data) use ($clientId) {
                $givenName = $data['given_name'] ?? $data['name'] ?? null;
                if (blank($givenName)) throw new InvalidArgumentException('Given Name is required for passenger.');

                $surname = $data['surname'] ?? null;
                $dobRaw  = $data['dob'] ?? null;
                $passExp = $data['passportExpiry'] ?? null;

                $dob      = $dobRaw ? Carbon::parse($dobRaw) : null;
                $passport = $passExp ? Carbon::parse($passExp) : null;

                $attributes = [
                    'title'        => $data['title'] ?? null,
                    'given_name'   => $givenName,
                    'surname'      => $surname,
                    'dob'          => $dob,
                    'nationality'  => $data['nationality'] ?? null,
                    'passport_no'  => $data['passportNumber'] ?? null,
                    'passport_exp' => $passport,
                    'client_id'    => $clientId,
                ];

                $lookup = [
                    'client_id'   => $clientId,
                    'passport_no' => $attributes['passport_no'],
                ];

                if (empty($lookup['passport_no'])) {
                    $lookup = [
                        'client_id'  => $clientId,
                        'given_name' => $givenName,
                        'surname'    => $surname,
                        'dob'        => $dob,
                    ];
                }

                return Passenger::updateOrCreate($lookup, $attributes);
            });
        });
    }
    public function sendEmailToUser($user)
    {
        // dd($userData);
        $user = $userData['user'];
        $username = $user['userFullName'] ?? '-';
        $userEmail = $user['userEmail'] ?? null;
        $userDetails = BookingId::create([
            'name' => $username,
            'email' => $userEmail,
            'phone_code' => $user['userPhoneCode'] ?? '-',
            'phone' => $user['userPhone'] ?? '-',
            'acceptOffers' => $user['acceptOffers'] ?? false,
            'booking_id' => $userData['bookingRefID'] ?? '-',
            'airline' => $userData['airline'] ?? null,
            'airline_ids' => $userData['airlineIds'] ?? null,
            'ticket_limit' => $userData['ticketLimit'] ?? null,
            'payment_limit' => $userData['paymentLimit'] ?? null,
            // 'is_paid' => false,
            'ip' => request()->ip(),
        ]);
        $emailMsg = null;
        if ($userEmail && $userData['ticketStatusMsg']) {
            try {
                Mail::to($userEmail)->send(new SendMail($username, $bookingRefID, $userData['ticketStatusMsg']));
                $emailMsg = 'Flight details sent to email successfully';
            } catch (\Exception $e) {
                Log::error('Mail sending failed: ' . $e->getMessage());
                $emailMsg = 'Failed to send email';
            }
        }
        return [
            'user' => $userDetails,
            'emailMessage' => $emailMsg,
        ];
    }









    // public function sendEmailToUser($user)
    // {
    //     $emailMsg = null;
    //     if ($userEmail && $userData['ticketStatusMsg']) {
    //         try {
    //             Mail::to($userEmail)->send(new SendMail($username, $bookingRefID, $userData['ticketStatusMsg']));
    //             $emailMsg = 'Flight details sent to email successfully';
    //         } catch (\Exception $e) {
    //             Log::error('Mail sending failed: ' . $e->getMessage());
    //             $emailMsg = 'Failed to send email';
    //         }
    //     }
    //     return [
    //         'user' => $userDetails,
    //         'emailMessage' => $emailMsg,
    //     ];
    // }
}
