<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Company\UpsertCompanyRequest;
use App\Models\CompanyProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\CompanyProfileChangeRequest;

/**
 * @group Customer
 */
class CompanyProfileController extends Controller
{
    /**
     * Get company profile
     *
     * Returns the authenticated customer's company profile (if set).
     *
     * @authenticated
     * @response 200 {

     *   "company_name": "ACME Sp. z o.o.",
     *   "vat_id": "PL1234567890",
     *   "regon": "123456789",
     *   "address_line1": "ul. Prosta 1",
     *   "address_line2": null,
     *   "postal_code": "00-000",
     *   "city": "Warszawa",
     *   "country": "PL",
     *   "phone": "+48 123 456 789",
     *   "description": "test"

     * }
     */
    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->companyProfile;

        return response()->json($profile ?: null);
    }

    /**
     * Upsert company profile
     *
     * Creates or updates the authenticated customer's company data (1:1 with user).
     * Returns the saved record.
     *
     * @authenticated
     * @bodyParam company_name string required The legal company name. Example: ACME Sp. z o.o.
     * @bodyParam vat_id string The VAT ID (e.g., NIP). Example: PL1234567890
     * @bodyParam regon string Optional business identifier. Example: 123456789
     * @bodyParam address_line1 string required Street and number. Example: ul. Prosta 1
     * @bodyParam address_line2 string Apartment/extra details. Example: lok. 12
     * @bodyParam postal_code string required Postal code. Example: 00-000
     * @bodyParam city string required City. Example: Warszawa
     * @bodyParam country string 2-letter ISO code. Default: PL. Example: PL
     * @bodyParam phone string Contact phone. Example: +48 123 456 789
     *
     * @response 200 {
     *   "id": 3,
     *   "user_id": 12,
     *   "company_name": "ACME Sp. z o.o.",
     *   "vat_id": "PL1234567890",
     *   "regon": "123456789",
     *   "address_line1": "ul. Prosta 1",
     *   "address_line2": null,
     *   "postal_code": "00-000",
     *   "city": "Warszawa",
     *   "country": "PL",
     *   "phone": "+48 123 456 789",
     *   "description": "test"
     * }
     */
    public function upsert(UpsertCompanyRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $data['country'] = $data['country'] ?? 'PL';

        // create or update 1:1
        $profile = $user->companyProfile()
            ->updateOrCreate(['user_id' => $user->id], $data);

        return response()->json($profile);
    }

    public function requestUpdate(Request $request)
{
    $user = $request->user();

    $profile = CompanyProfile::where('user_id', $user->id)->firstOrFail();

    $data = $request->validate([
        'company_name' => ['required', 'string', 'max:255'],
        'nip' => ['nullable', 'string', 'max:32'],
        'regon' => ['nullable', 'string', 'max:32'],
        'street' => ['required', 'string', 'max:255'],
        'building_number' => ['nullable', 'string', 'max:32'],
        'apartment_number' => ['nullable', 'string', 'max:32'],
        'postal_code' => ['required', 'string', 'max:20'],
        'city' => ['required', 'string', 'max:255'],
        'country' => ['required', 'string', 'max:255'],
        'phone' => ['nullable', 'string', 'max:64'],
        'email' => ['nullable', 'email', 'max:255'],
    ]);

    $hasPending = CompanyProfileChangeRequest::where('user_id', $user->id)
        ->where('status', 'pending')
        ->exists();

    if ($hasPending) {
        return response()->json([
            'message' => 'Masz już zgłoszenie oczekujące na akceptację.',
        ], 422);
    }

    $requestChange = CompanyProfileChangeRequest::create([
        'user_id' => $user->id,
        'company_profile_id' => $profile->id,
        'current_data' => $profile->toArray(),
        'requested_data' => $data,
        'status' => 'pending',
    ]);

    return response()->json([
        'message' => 'Zgłoszenie zmiany danych firmy zostało wysłane do akceptacji.',
        'data' => $requestChange,
    ]);
}
}
