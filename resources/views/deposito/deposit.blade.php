@extends('header') @section('header')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <div class="mt-2">
            <small>Data is now being loaded from Match Trader API. If you're seeing this error, please check your API connection or contact support.</small>
        </div>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- API Status indicator --}}
<div class="row mb-2">
    <div class="col-12">
        <small class="text-muted">
            <i class="fas fa-cloud"></i> Loading ALL data from Match Trader API 
            @if(isset($deposits) && count($deposits) > 0)
                <span class="badge bg-success">{{ count($deposits) }} deposits loaded</span>
            @else
                <span class="text-warning">(Loading all data may take a few moments...)</span>
            @endif
            <a href="{{route('testDepositApi')}}" target="_blank" class="text-info">(Test API Connection)</a>
        </small>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="row">
            <div class="col-md-6 px-2">
                <h4 class="fw-bolder">Deposits</h4>
            </div>

            <div class="col-md-3 px-2">
                <a href=""><i class="fa-regular fa-arrows-rotate-reverse fa-sm bg-info rounded p-3"></i></a>
            </div>
            <div class="col-md-3 px-2">
                <a href="{{route('depositadd')}}"><i class="fa-duotone fa-solid fa-user-plus fa-sm bg-success rounded p-3" style="--fa-primary-color: #ffffff; --fa-secondary-color: #ffffff; --fa-secondary-opacity: 1;"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <form method="POST" class="form" action="{{route('depositSearch')}}">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="input-group">
                        <input class="form-control border-right-0 border" type="search" placeholder="Enter Keyword" name="search" value="{{request('search')}}" />
                        <button type="submit" class="input-group-text bg-info border-0"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div class="col-md-4">
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{request('date_from')}}" />
                </div>
                <div class="col-md-4">
                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{request('date_to')}}" />
                </div>
            </div>
        </form>
    </div>
    <div class="col-md-3">
        <div class="row">
            <div class="col-md-2">
                <a href=""><i class="fa-duotone fa-solid fa-down-from-bracket fa-sm bg-danger rounded p-3" style="--fa-primary-color: #ffffff; --fa-secondary-color: #ffffff; --fa-secondary-opacity: 1;"></i></a>
            </div>
        </div>
    </div>
</div>
@endsection @section('body')
<div class="card">
    <div class="col-md-12 table-responsive table-responsive-sm">
        <table class="table table-hover table-sm" id="depositsTable">
            <caption>
                List of deposits
            </caption>
            <form method="POST" class="form align-items-center" action="">
                <thead class="bg-dark report-white-font">
                    <tr>
                        <th><input type="search" class="form-control" id="autoSizingInputGroup" placeholder="Trading Account" /></th>
                        <th><input type="search" class="form-control" id="autoSizingInputGroup" placeholder="Email" /></th>

                        <th><input class="form-control" type="search" placeholder="Name" id="example-search-input" /></th>
                        <th><input class="form-control" type="search" placeholder="Surname" id="example-search-input" /></th>
                        <th>Amount</th>
                        <th>Net Amount</th>
                        <th><input class="form-control" type="search" placeholder=" Created" id="example-search-input" /></th>
                        <th><input class="form-control" type="search" placeholder="Currency" id="example-search-input" /></th>
                        <th><input class="form-control" type="search" placeholder=" Status" id="example-search-input" /></th>
                        <th><input class="form-control" type="search" placeholder="Payment Getway" id="example-search-input" /></th>
                        <th><input class="form-control" type="search" placeholder="Payment Id" id="example-search-input" /></th>
                        <th><input class="form-control" type="search" placeholder="Last contact" id="example-search-input" /></th>
                        <th><input class="form-control" type="search" placeholder="Offer" id="example-search-input" /></th>
                        <th><input class="form-control" type="search" placeholder="Crypto address" id="example-search-input" /></th>
                        <th><input class="form-control" type="search" placeholder="Reference coad" id="example-search-input" /></th>
                        <th><input class="form-control" type="search" placeholder="Lead source" id="example-search-input" /></th>
                        <th>
                            <!-- Button trigger modal -->
                            <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#add">
                                <i class="fa-duotone fa-solid fa-user-plus fa-sm bg-success rounded p-3" style="--fa-primary-color: #ffffff; --fa-secondary-color: #ffffff; --fa-secondary-opacity: 1;"></i>
                            </button>

                            <!-- Modal -->
                            <div class="modal fade" id="add" tabindex="-1" aria-labelledby="addLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="addLabel">Select columns to show</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <select class="selectpicker" multiple aria-label="size 3 select example">
                                                <option value="">Account Manager</option>
                                                <option value="">Last contact</option>
                                                <option value="">Name</option>
                                                <option value="">Surname</option>
                                                <option value="">Trading account</option>
                                                <option value="">Email</option>
                                                <option value="">Created</option>
                                                <option value="">Status</option>
                                                <option value="">Offer</option>
                                                <option value="">Amount</option>
                                                <option value="">Net amount</option>
                                                <option value="">Currency</option>
                                                <option value="">Payment gatway</option>
                                                <option value="">Crypto address</option>
                                                <option value="">Reference coad</option>
                                                <option value="">Payment id</option>
                                                <option value="">Lead source</option>
                                            </select>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-primary">Save changes</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </th>
                    </tr>
                </thead>
            </form>
            <tbody>
                @forelse($deposits ?? [] as $deposit)
                <tr>
                    <td>{{ $deposit->trading_account }}</td>
                    <td><a href="{{route('clientProfile', ['email' => $deposit->email])}}" class="text-primary">{{ $deposit->email }}</a></td>
                    <td>{{ $deposit->first_name }}</td>
                    <td>{{ $deposit->last_name }}</td>
                    <td>${{ number_format($deposit->amount, 2) }}</td>
                    <td>${{ number_format($deposit->net_amount, 2) }}</td>
                    <td>{{ $deposit->created_at->format('d.m.Y') }}</td>
                    <td>{{ $deposit->currency }}</td>
                    <td>
                        <span class="badge 
                            @if($deposit->status == 'approved') bg-success
                            @elseif($deposit->status == 'pending') bg-warning
                            @elseif($deposit->status == 'rejected') bg-danger
                            @else bg-info
                            @endif">
                            {{ ucfirst($deposit->status) }}
                        </span>
                    </td>
                    <td><a href="{{route('DipoPaymentGte')}}" class="text-primary">{{ $deposit->payment_gateway }}</a></td>
                    <td>{{ $deposit->payment_id ?? 'N/A' }}</td>
                    <td>{{ $deposit->last_contact ? $deposit->last_contact->format('d.m.Y') : 'N/A' }}</td>
                    <td>{{ $deposit->offer ?? 'Standard' }}</td>
                    <td>{{ $deposit->crypto_address ?? ($deposit->country ?? 'N/A') }}</td>
                    <td>{{ $deposit->reference_code ?? 'N/A' }}</td>
                    <td>{{ $deposit->lead_source ?? ($deposit->remark ?? 'N/A') }}</td>
                    <td>
                        <div class="btn-group" role="group">
                            @if(strtolower($deposit->status) !== 'approved' && strtolower($deposit->status) !== 'done')
                                <button type="button" class="btn btn-success btn-sm" 
                                        onclick="acceptDeposit('{{ $deposit->id }}', '{{ $deposit->email }}')"
                                        title="Accept Deposit">
                                    <i class="fas fa-check"></i> Accept
                                </button>
                            @endif
                            
                            @if(strtolower($deposit->status) !== 'rejected')
                                <button type="button" class="btn btn-danger btn-sm" 
                                        onclick="rejectDeposit('{{ $deposit->id }}', '{{ $deposit->email }}')"
                                        title="Reject Deposit">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            @endif
                            
                            <a href="{{route('detailesDeposit', ['id' => $deposit->id])}}" 
                               class="btn btn-info btn-sm" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="17" class="text-center py-4">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            @if(session('error'))
                                Unable to load deposits from API. Please check your connection and try again.
                                <br><a href="{{route('depositView')}}" class="alert-link">Retry</a>
                            @else
                                No deposits found from API. <a href="{{route('depositadd')}}" class="alert-link">Add your first deposit</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Accept Deposit Modal --}}
<div class="modal fade" id="acceptModal" tabindex="-1" aria-labelledby="acceptModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="acceptModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Accept Deposit
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="acceptForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle me-2"></i>
                        Are you sure you want to accept this deposit?
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong> <span id="acceptEmail"></span>
                    </div>
                    <div class="mb-3">
                        <label for="acceptNote" class="form-label">Additional Notes (Optional)</label>
                        <textarea class="form-control" id="acceptNote" name="note" rows="3" 
                                  placeholder="Add any notes about this acceptance..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>Accept Deposit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reject Deposit Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="fas fa-times-circle me-2"></i>Reject Deposit
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Are you sure you want to reject this deposit?
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong> <span id="rejectEmail"></span>
                    </div>
                    <div class="mb-3">
                        <label for="rejectReason" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectReason" name="reason" rows="3" 
                                  placeholder="Please provide a reason for rejecting this deposit..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i>Reject Deposit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function acceptDeposit(depositId, email) {
    document.getElementById('acceptEmail').textContent = email;
    document.getElementById('acceptForm').action = '{{ route("depositAccept", ":id") }}'.replace(':id', depositId);
    
    const acceptModal = new bootstrap.Modal(document.getElementById('acceptModal'));
    acceptModal.show();
}

function rejectDeposit(depositId, email) {
    document.getElementById('rejectEmail').textContent = email;
    document.getElementById('rejectForm').action = '{{ route("depositReject", ":id") }}'.replace(':id', depositId);
    
    const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
    rejectModal.show();
}

// Handle accept form submission with AJAX
document.getElementById('acceptForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalBtnContent = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            location.reload(); // Refresh to show updated data
        } else {
            if (data.api_limitation) {
                alert('ℹ️ ' + data.message);
            } else {
                alert('❌ ' + data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ An error occurred while processing the request.');
    })
    .finally(() => {
        submitBtn.innerHTML = originalBtnContent;
        submitBtn.disabled = false;
        bootstrap.Modal.getInstance(document.getElementById('acceptModal')).hide();
    });
});

// Handle reject form submission with AJAX
document.getElementById('rejectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalBtnContent = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            location.reload(); // Refresh to show updated data
        } else {
            if (data.api_limitation) {
                alert('ℹ️ ' + data.message);
            } else {
                alert('❌ ' + data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ An error occurred while processing the request.');
    })
    .finally(() => {
        submitBtn.innerHTML = originalBtnContent;
        submitBtn.disabled = false;
        bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
    });
});
</script>

@endsection
