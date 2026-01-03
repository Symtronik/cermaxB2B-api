<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Company\UpsertCompanyRequest;
use App\Models\CompanyProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
