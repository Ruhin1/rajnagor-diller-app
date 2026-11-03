@extends('adminlte::page')

@section('title', 'খরচ ম্যানেজমেন্ট')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">খরচ ম্যানেজমেন্ট</h1>
        <button id="addExpenseBtn" class="btn btn-success">
            <i class="fas fa-plus-circle mr-1"></i> নতুন খরচ যুক্ত করুন
        </button>
    </div>
@stop

@section('content')
<div class="container-fluid">
<button 
    onclick="printViaBluetooth(6)" 
    class="btn btn-primary"
>
    <i class="fas fa-print"></i> Bluetooth Print
</button>
<!-- Loading Modal -->
<div id="bluetoothPrintModal" class="bluetooth-print-modal">
    <div class="modal-content">
        <div class="spinner"></div>
        <h3>Bluetooth Printer খুলছে...</h3>
        <p>দয়া করে অপেক্ষা করুন</p>
        <button onclick="closeBluetoothModal()" class="btn btn-secondary mt-3">
            বাতিল
        </button>
    </div>
</div>
</div> 
<a 
    href="javascript:void(0)" 
    onclick="printViaBluetooth(6)"
    class="btn btn-success"
>
    📱 Mobile Print (Bluetooth)
</a>
@stop

@section('css')
<style>
.bluetooth-print-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.bluetooth-print-modal.active {
    display: flex;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 10px;
    text-align: center;
    max-width: 400px;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
    margin: 20px auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/**
 * Bluetooth প্রিন্টিং function
 */
function printViaBluetooth(invoiceId) {
    // Modal দেখান
    showBluetoothModal();
    
    // API call করুন
    fetch('/api/bluetooth-print?invoice_id=' + invoiceId, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // RawBT app খুলুন
            window.location.href = data.intent_url;
            
            // 2 second পর modal বন্ধ করুন
            setTimeout(() => {
                closeBluetoothModal();
            }, 2000);
        } else {
            alert('Error: ' + data.message);
            closeBluetoothModal();
        }
    })
    .catch(error => {
        console.error('Bluetooth Print Error:', error);
        alert('প্রিন্টিং এ সমস্যা হয়েছে। আবার চেষ্টা করুন।');
        closeBluetoothModal();
    });
}

function showBluetoothModal() {
    document.getElementById('bluetoothPrintModal').classList.add('active');
}

function closeBluetoothModal() {
    document.getElementById('bluetoothPrintModal').classList.remove('active');
}

/**
 * RawBT app না থাকলে install করার সুবিধা
 */
function checkRawBTInstalled() {
    // Play Store link
    const playStoreUrl = 'https://play.google.com/store/apps/details?id=ru.a402d.rawbtprinter';
    
    // Check করুন app আছে কিনা (Android WebView এ)
    if (typeof Android !== 'undefined' && Android.isAppInstalled) {
        if (!Android.isAppInstalled('ru.a402d.rawbtprinter')) {
            if (confirm('RawBT app পাওয়া যায়নি। Play Store থেকে install করবেন?')) {
                window.location.href = playStoreUrl;
            }
        }
    }
}

// Page load হলে check করুন
document.addEventListener('DOMContentLoaded', function() {
    checkRawBTInstalled();
});
</script>
@stop