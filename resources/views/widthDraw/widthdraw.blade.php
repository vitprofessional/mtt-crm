@extends('header') 

@section('header')
<div class="row mt-4">
    <div class="col-md-3">
        <div class="row">
            <div class="col-md-7 px-2">
                <h4 class="fw-bolder">Withdrawals</h4>
                <small class="text-muted">Total: {{ $withdrawals->count() }} withdrawals</small>
            </div>
            <div class="col-md-2 px-2">
                <a href="{{ route('widthdrawView') }}" title="Refresh">
                    <i class="fa-regular fa-arrows-rotate-reverse fa-sm bg-info rounded p-3"></i>
                </a>
            </div>
            <div class="col-md-3 px-2">
                <a href="{{route('widthdrawAdd')}}" title="Add Withdrawal">
                    <i class="fa-duotone fa-solid fa-user-plus fa-sm bg-success rounded p-3" style="--fa-primary-color: #ffffff; --fa-secondary-color: #ffffff; --fa-secondary-opacity: 1;"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <form method="POST" class="form" action="{{ route('withdrawalSearch') }}">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="input-group">
                        <input class="form-control border-right-0 border" type="search" 
                               placeholder="Enter Email" name="email" value="{{ request('email') }}" />
                        <div class="input-group-text bg-info"><i class="fa fa-search"></i></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <input type="date" class="form-control" name="date_from" 
                           value="{{ request('date_from') }}" placeholder="From Date" />
                </div>
                <div class="col-md-4">
                    <input type="date" class="form-control" name="date_to" 
                           value="{{ request('date_to') }}" placeholder="To Date" />
                </div>
            </div>
        </form>
    </div>
    <div class="col-md-3">
        <div class="row">
            <div class="col-md-4">
                <a href="{{ route('testWithdrawalApi') }}" target="_blank" title="Test API">
                    <i class="fa-duotone fa-solid fa-plug fa-sm bg-warning rounded p-3" style="--fa-primary-color: #ffffff; --fa-secondary-color: #ffffff;"></i>
                </a>
            </div>
            <div class="col-md-4">
                <a href="" title="Export">
                    <i class="fa-duotone fa-solid fa-down-from-bracket fa-sm bg-danger rounded p-3" style="--fa-primary-color: #ffffff; --fa-secondary-color: #ffffff; --fa-secondary-opacity: 1;"></i>
                </a>
            </div>
        </div>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@endsection 

@section('body')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="withdrawalsTable">
                <thead class="thead-custom">
                    <tr>
                        <th class="text-center" style="width: 8%;">ID</th>
                        <th style="width: 20%;">Email</th>
                        <th style="width: 15%;">Name</th>
                        <th class="text-end" style="width: 12%;">Amount</th>
                        <th class="text-center" style="width: 8%;">Currency</th>
                        <th class="text-center" style="width: 10%;">Status</th>
                        <th class="text-center" style="width: 12%;">Created</th>
                        <th class="text-center" style="width: 10%;">Gateway</th>
                        <th class="text-center" style="width: 15%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if($withdrawals->isEmpty())
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div>
                                    <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Withdrawals Found</h5>
                                    <p class="text-muted">{{ isset($error) ? $error : 'There are currently no withdrawal records to display.' }}</p>
                                    <a href="{{ route('widthdrawAdd') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i>Add New Withdrawal
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @else
                        @foreach($withdrawals as $withdrawal)
                        <tr class="withdrawal-row">
                            <td class="text-center">
                                <span class="badge bg-primary px-2 py-1" style="font-size: 0.75rem;">
                                    {{ substr($withdrawal['uuid'] ?? 'W-' . rand(1000, 9999), 0, 8) }}
                                </span>
                            </td>
                            <td>
                                @if(isset($withdrawal['accountInfo']['email']))
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-envelope text-muted me-2" style="font-size: 0.8rem;"></i>
                                        <a href="{{ route('clientProfile', ['email' => $withdrawal['accountInfo']['email']]) }}" 
                                           class="text-decoration-none text-primary fw-medium">
                                            {{ $withdrawal['accountInfo']['email'] }}
                                        </a>
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">No email</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user text-muted me-2" style="font-size: 0.8rem;"></i>
                                    <div>
                                        <div class="fw-medium text-dark">
                                            {{ $withdrawal['accountInfo']['personalDetails']['firstname'] ?? 'N/A' }}
                                            {{ $withdrawal['accountInfo']['personalDetails']['lastname'] ?? '' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="fw-bold text-danger fs-6">
                                    ${{ number_format($withdrawal['paymentRequestInfo']['financialDetails']['amount'] ?? 0, 2) }}
                                </div>
                                @if(isset($withdrawal['paymentRequestInfo']['financialDetails']['netAmount']) && $withdrawal['paymentRequestInfo']['financialDetails']['netAmount'] != $withdrawal['paymentRequestInfo']['financialDetails']['amount'])
                                    <small class="text-muted d-block">
                                        Net: ${{ number_format($withdrawal['paymentRequestInfo']['financialDetails']['netAmount'], 2) }}
                                    </small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info text-white px-2 py-1">
                                    {{ $withdrawal['paymentRequestInfo']['financialDetails']['currency'] ?? 'USD' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @php
                                    $status = $withdrawal['paymentRequestInfo']['financialDetails']['status'] ?? 'pending';
                                    $statusClass = match(strtolower($status)) {
                                        'approved', 'completed', 'processed', 'done' => 'success',
                                        'rejected', 'declined', 'failed' => 'danger',
                                        'pending', 'processing' => 'warning',
                                        default => 'secondary'
                                    };
                                    $statusIcon = match(strtolower($status)) {
                                        'approved', 'completed', 'processed', 'done' => 'fa-check-circle',
                                        'rejected', 'declined', 'failed' => 'fa-times-circle',
                                        'pending', 'processing' => 'fa-clock',
                                        default => 'fa-question-circle'
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }} px-2 py-1 d-flex align-items-center justify-content-center gap-1" style="font-size: 0.75rem;">
                                    <i class="fas {{ $statusIcon }}" style="font-size: 0.7rem;"></i>
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="text-muted" style="font-size: 0.85rem;">
                                    @if(isset($withdrawal['created']))
                                        <div class="fw-medium">{{ \Carbon\Carbon::parse($withdrawal['created'])->format('M d, Y') }}</div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($withdrawal['created'])->format('h:i A') }}</small>
                                    @else
                                        <span class="fst-italic">N/A</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-credit-card text-muted me-2" style="font-size: 0.8rem;"></i>
                                    <span class="badge bg-light text-dark px-2 py-1" style="font-size: 0.75rem;">
                                        {{ $withdrawal['paymentRequestInfo']['paymentGatewayDetails']['name'] ?? 'Bank Transfer' }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    @if(strtolower($withdrawal['paymentRequestInfo']['financialDetails']['status'] ?? 'pending') === 'pending')
                                        <button type="button" class="btn btn-outline-success btn-sm px-3" 
                                                onclick="acceptWithdrawal('{{ $withdrawal['uuid'] ?? rand(1000, 9999) }}', '{{ $withdrawal['accountInfo']['email'] ?? 'N/A' }}')"
                                                title="Accept Withdrawal">
                                            <i class="fas fa-check me-1"></i>Accept
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm px-3" 
                                                onclick="rejectWithdrawal('{{ $withdrawal['uuid'] ?? rand(1000, 9999) }}', '{{ $withdrawal['accountInfo']['email'] ?? 'N/A' }}')"
                                                title="Reject Withdrawal">
                                            <i class="fas fa-times me-1"></i>Reject
                                        </button>
                                    @endif
                                    <a href="{{ route('widthdrawDetailes') }}" class="btn btn-info btn-sm" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        
        {{-- Debug Information --}}
        @if(config('app.debug'))
        <div class="mt-3">
            <small class="text-muted">
                Debug: Found {{ $withdrawals->count() }} withdrawals
                @if(isset($error))
                    | Error: {{ $error }}
                @endif
            </small>
        </div>
        @endif
    </div>
</div>

{{-- Accept Withdrawal Modal --}}
<div class="modal fade" id="acceptModal" tabindex="-1" aria-labelledby="acceptModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="acceptModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Accept Withdrawal
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="acceptForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle me-2"></i>
                        Are you sure you want to accept this withdrawal?
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong> <span id="acceptEmail"></span>
                    </div>
                    <div class="mb-3">
                        <label for="acceptNotes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="acceptNotes" name="notes" rows="3" 
                                  placeholder="Add any notes about this acceptance..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>Accept Withdrawal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reject Withdrawal Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="fas fa-times-circle me-2"></i>Reject Withdrawal
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Are you sure you want to reject this withdrawal?
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong> <span id="rejectEmail"></span>
                    </div>
                    <div class="mb-3">
                        <label for="rejectReason" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectReason" name="reason" rows="3" 
                                  placeholder="Please provide a reason for rejecting this withdrawal..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i>Reject Withdrawal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function acceptWithdrawal(withdrawalId, email) {
    document.getElementById('acceptEmail').textContent = email;
    document.getElementById('acceptForm').action = '{{ route("withdrawalAccept", ":id") }}'.replace(':id', withdrawalId);
    
    const acceptModal = new bootstrap.Modal(document.getElementById('acceptModal'));
    acceptModal.show();
}

function rejectWithdrawal(withdrawalId, email) {
    document.getElementById('rejectEmail').textContent = email;
    document.getElementById('rejectForm').action = '{{ route("withdrawalReject", ":id") }}'.replace(':id', withdrawalId);
    
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

<style>
/* Professional Table Styling */
#withdrawalsTable {
    border-collapse: separate;
    border-spacing: 0;
    background-color: white;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    overflow: hidden;
}

/* Custom header styling */
.thead-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.thead-custom th {
    background: transparent !important;
    color: white !important;
    font-weight: 600 !important;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
    padding: 15px 12px !important;
    border: none !important;
    position: relative;
}

.thead-custom th:first-child {
    border-top-left-radius: 10px;
}

.thead-custom th:last-child {
    border-top-right-radius: 10px;
}

/* Row styling */
.withdrawal-row {
    transition: all 0.3s ease;
    border-bottom: 1px solid #f8f9fa;
}

.withdrawal-row:hover {
    background-color: #f8f9fa !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.withdrawal-row td {
    padding: 15px 12px !important;
    vertical-align: middle !important;
    border-left: none !important;
    border-right: none !important;
    border-top: none !important;
}

/* Badge improvements */
.badge {
    font-weight: 500;
    border-radius: 6px;
}

/* Button improvements */
.btn-outline-success, .btn-outline-danger {
    border-width: 1px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-outline-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.btn-outline-danger:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
}

/* Status badge animations */
.badge {
    transition: all 0.3s ease;
}

.badge:hover {
    transform: scale(1.05);
}

/* Card improvements */
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.card-body {
    padding: 2rem;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .withdrawal-row td {
        padding: 10px 8px !important;
        font-size: 0.85rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.7rem;
    }
}
</style>
@endsection





