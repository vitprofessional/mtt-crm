@extends('header') 
@section('header')
<div class="row mt-4">
    <div class="col-md-3">
        <div class="row">
            <div class="col-md-6 px-2">
                <h4 class="fw-bolder">Clients</h4>
            </div>
            <div class="col-md-3 px-2">
                <a href=""><i class="fa-regular fa-arrows-rotate-reverse fa-sm bg-info rounded p-3"></i></a>
            </div>
            <div class="col-md-3 px-2">
                <a href="{{route('addclient')}}"><i class="fa-duotone fa-solid fa-user-plus fa-sm bg-success rounded p-3" style="--fa-primary-color: #ffffff; --fa-secondary-color: #ffffff; --fa-secondary-opacity: 1;"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <form method="POST" class="form" action="">
            <div class="row">
                <div class="col-md-4">
                    <div class="input-group">
                        <input class="form-control border-right-0 border" type="search" placeholder="Enter Keyword" id="example-search-input" />
                        <div class="input-group-text bg-info"><i class="fa fa-search"></i></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <input type="date" class="form-control" id="to" placeholder="" name="to" />
                </div>
                <div class="col-md-4">
                    <input type="date" class="form-control" id="form" placeholder="" name="form" />
                </div>
            </div>
        </form>
    </div>
    <div class="col-md-3">
        <div class="row">
            <div class="col-md-2">
                <a href=""><i class="fa-duotone fa-solid fa-arrow-up-from-bracket fa-sm bg-warning rounded p-3" style="--fa-primary-color: #ffffff; --fa-secondary-color: #ffffff; --fa-secondary-opacity: 1;"></i></a>
            </div>
            <div class="col-md-2">
                <a href=""><i class="fa-duotone fa-solid fa-down-from-bracket fa-sm bg-danger rounded p-3" style="--fa-primary-color: #ffffff; --fa-secondary-color: #ffffff; --fa-secondary-opacity: 1;"></i></a>
            </div>
            <div class="col-md-7 d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="button" class="btn btn-info text-uppercase btn-sm rounded">bulk operation</button>
            </div>
        </div>
    </div>
</div>
@endsection 
@section('body')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fa fa-users"></i> Client Management
            <span class="badge badge-success ml-2" id="totalClients">{{ count($clients ?? []) }} Total Clients</span>
            <small class="text-muted">(All client data loaded)</small>
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive-enhanced">
            <table class="table table-hover table-sm" id="clientsTable">
                <caption>List of all clients</caption>
                <thead class="bg-dark report-white-font">
                    <tr>
                        <th><input type="search" class="form-control form-control-sm" placeholder="Online"></th>
                        <th><input type="search" class="form-control form-control-sm" placeholder="Email"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Name"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Surname"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Created"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Last contact"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Status"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Manager"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Branch"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Affiliate"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Phone number"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Language"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Role"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Lead status"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Lead source"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Last online"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Free margin"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Equity"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Margin level"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Total deposits"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Total withdrawals"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Last deposit"></th>
                        <th><input class="form-control form-control-sm" type="search" placeholder="Last note"></th>
                    </tr>
                </thead>

                <tbody>
                    @if(empty($clients))
                        <tr>
                            <td colspan="23" class="text-center text-muted">
                                <p>No clients found.</p>
                                <small>Debug: {{ isset($clients) ? 'Variable exists but empty' : 'Variable not set' }}</small>
                            </td>
                        </tr>
                    @endif
                    @foreach($clients as $client)
                        <tr>
                            <td>
                                <span class="badge bg-success">Active</span>
                            </td>
                            <td>
                                <a href="{{ route('clientProfile',['email' => $client['email']]) }}" class="text-primary">
                                    {{ $client['email'] ?? 'N/A' }}
                                </a>
                            </td>
                            <td>{{ $client['firstName'] ?? 'N/A' }}</td>
                            <td>{{ $client['lastName'] ?? 'N/A' }}</td>
                            <td>
                                <small>
                                    {{ isset($client['created']) ? \Carbon\Carbon::parse($client['created'])->format('d.m.Y') : 'N/A' }}
                                </small>
                            </td>
                            <td>
                                <small>
                                    {{ isset($client['lastContact']) && $client['lastContact'] ? \Carbon\Carbon::parse($client['lastContact'])->format('d.m.Y') : 'Never' }}
                                </small>
                            </td>
                            <td>
                                <span class="badge {{ ($client['status'] ?? '') === 'NEW' ? 'bg-info' : (($client['status'] ?? '') === 'VERIFIED' ? 'bg-success' : 'bg-secondary') }}">
                                    {{ $client['status'] ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $client['manager'] ?? 'Unassigned' }}</small>
                            </td>
                            <td>
                                <small>{{ $client['branch'] ?? 'N/A' }}</small>
                            </td>
                            <td>{{ $client['affiliate'] ?? 'N/A' }}</td>
                            <td>{{ $client['phone'] ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ strtoupper($client['language'] ?? 'EN') }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $client['role'] ?? 'N/A' }}</small>
                            </td>
                            <td>{{ $client['leadStatus'] ?? $client['status'] ?? 'N/A' }}</td>
                            <td>{{ $client['leadSource'] ?? $client['country'] ?? 'N/A' }}</td>
                            <td>{{ $client['lastOnline'] ?? 'N/A' }}</td>
                            <td>{{ $client['freeMargin'] ?? 'N/A' }}</td>
                            <td>{{ $client['equity'] ?? 'N/A' }}</td>
                            <td>{{ $client['marginLevel'] ?? 'N/A' }}</td>
                            <td>{{ $client['totalDeposits'] ?? 'N/A' }}</td>
                            <td>{{ $client['totalWithdrawals'] ?? 'N/A' }}</td>
                            <td>{{ $client['lastDeposit'] ?? 'N/A' }}</td>
                            <td>Last note</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update the total clients count
    const totalClients = document.getElementById('totalClients');
    const tableRows = document.querySelectorAll('#clientsTable tbody tr');
    const actualCount = tableRows.length;
    
    // Only count rows that are not the "no data" row
    const noDataRow = document.querySelector('#clientsTable tbody tr td[colspan="23"]');
    const finalCount = noDataRow ? 0 : actualCount;
    
    totalClients.textContent = `${finalCount} Total Clients`;
    
    // Add loading indicator functionality for refresh button
    const refreshButton = document.querySelector('.fa-arrows-rotate-reverse').parentElement;
    if (refreshButton) {
        refreshButton.addEventListener('click', function(e) {
            e.preventDefault();
            totalClients.textContent = 'Loading...';
            totalClients.className = 'badge badge-warning ml-2';
            
            // Reload the page
            setTimeout(() => {
                window.location.reload();
            }, 500);
        });
    }
});
</script>
@endsection
