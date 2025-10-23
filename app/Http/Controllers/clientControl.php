<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class clientControl extends Controller
{
    public function client(){
        // Debug the URL construction
        $baseUrl = env('API_BASE_URL');
        $fullUrl = rtrim($baseUrl, '/') . '/accounts';
        
        \Log::info('Client API Request:', [
            'base_url' => $baseUrl,
            'full_url' => $fullUrl,
            'authorization' => env('API_AUTHORIZATION')
        ]);

        // Get ALL clients by handling pagination
        $allClients = $this->getAllClientsFromAPI($fullUrl);
        
        if (empty($allClients)) {
            \Log::error('No clients retrieved from API, using empty array');
            $clients = [];
            return view('clients.client', compact('clients'));
        }

        \Log::info('Final Client Data:', [
            'clients_count' => count($allClients),
            'first_client' => $allClients[0] ?? 'No clients found'
        ]);

        return view('clients.client', ['clients' => $allClients]);
    }

    private function getAllClientsFromAPI($fullUrl) {
        $allClients = [];
        $page = 0;
        $size = 500; // Large page size for efficiency
        $hasMoreData = true;

        \Log::info('Starting client fetching with full pagination', [
            'page_size' => $size,
            'note' => 'Fetching ALL client data as requested'
        ]);

        while ($hasMoreData) {
            $response = Http::withHeaders([
                'Content-Type'  => env('API_CONTENT_TYPE'),
                'Authorization' => 'Bearer ' . env('API_AUTHORIZATION'),
            ])->get($fullUrl, [
                'accountType' => 'CLIENT',
                'page' => $page,
                'size' => $size
            ]);

            if (!$response->successful()) {
                \Log::error('Client API Error:', [
                    'page' => $page,
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'url' => $fullUrl
                ]);
                break;
            }

            $json = $response->json();
            
            // Debug the API response structure
            \Log::info('Client API Response Structure for page ' . $page, [
                'total_elements' => $json['totalElements'] ?? 'unknown',
                'total_pages' => $json['totalPages'] ?? 'unknown',
                'current_page' => $page,
                'content_count' => count($json['content'] ?? [])
            ]);

            // Many APIs return a paged list under "content"; fall back to root if not.
            $items = $json['content'] ?? $json;
            if (!is_array($items)) {
                $items = [];
            }

            if (empty($items)) {
                $hasMoreData = false;
                break;
            }

            // Helper function to safely get string values
            $safeString = function($value) {
                if (is_array($value) || is_object($value)) {
                    return json_encode($value);
                }
                return $value ? (string) $value : null;
            };

            // Map API response to match the client data structure
            $pageClients = collect($items)->map(function ($i) use ($safeString) {
                return [
                    'email'        => $safeString(data_get($i, 'email')),
                    'firstName'    => $safeString(data_get($i, 'personalDetails.firstname')),
                    'lastName'     => $safeString(data_get($i, 'personalDetails.lastname')),
                    'created'      => $safeString(data_get($i, 'created')),
                    'lastContact'  => $safeString(data_get($i, 'contactDetails.toContact.toContactDate')),
                    'status'       => $safeString(data_get($i, 'verificationStatus') ?? data_get($i, 'leadDetails.status')),
                    'manager'      => $safeString(data_get($i, 'accountConfiguration.accountManager')),
                    'affiliate'    => $safeString(data_get($i, 'accountConfiguration.partnerId')),
                    'branch'       => $safeString(data_get($i, 'accountConfiguration.branchUuid')),
                    'country'      => $safeString(data_get($i, 'addressDetails.country')),
                    'phone'        => $safeString(data_get($i, 'contactDetails.phoneNumber')),
                    'language'     => $safeString(data_get($i, 'personalDetails.language')),
                    'role'         => $safeString(data_get($i, 'accountConfiguration.roleUuid')),
                    // Additional fields that might be computed or fetched separately
                    'lastOnline'     => null,
                    'freeMargin'     => null,
                    'equity'         => null,
                    'marginLevel'    => null,
                    'totalDeposits'  => null,
                    'totalWithdrawals'=> null,
                    'lastDeposit'    => null,
                    'leadStatus'     => $safeString(data_get($i, 'leadDetails.status')),
                    'leadSource'     => $safeString(data_get($i, 'leadDetails.source')),
                ];
            })->all();

            $allClients = array_merge($allClients, $pageClients);

            // Check if there are more pages
            if (isset($json['totalPages']) && $page >= ($json['totalPages'] - 1)) {
                $hasMoreData = false;
            } elseif (count($items) < $size) {
                // If we got fewer items than requested, we've reached the end
                $hasMoreData = false;
            } else {
                $page++;
            }
        }

        \Log::info('Client fetching completed', [
            'total_fetched' => count($allClients),
            'pages_processed' => $page + 1,
            'note' => 'ALL client data retrieved successfully'
        ]);

        return $allClients;
    }

    public function addclient(){
        return view('clients.addClient');
    }
}
