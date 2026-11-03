@extends('adminlte::page')

@section('title', 'একাউন্ট ম্যানেজমেন্ট')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">একাউন্ট ম্যানেজমেন্ট</h1>
        <a class="btn btn-primary" data-toggle="collapse" href="#addAccountCard" role="button" aria-expanded="false" aria-controls="addAccountCard">
            <i class="fas fa-user-plus mr-1"></i> নতুন একাউন্ট যুক্ত করুন
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    {{-- নতুন একাউন্ট যোগ করার কলাপসিবল কার্ড --}}
    <div class="collapse {{ $errors->any() && old('is_new_account') ? 'show' : '' }}" id="addAccountCard">
        <div class="card card-primary shadow-sm mb-4">
            <div class="card-header"><h3 class="card-title">নতুন একাউন্টের তথ্য দিন</h3></div>
            <div class="card-body">
                <form action="{{ route('acount.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="is_new_account" value="1">
                    <div class="row">
                        <div class="col-md-6"><div class="form-group">
                            <label for="name">সম্পূর্ণ নাম <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div></div>
                        <div class="col-md-6"><div class="form-group">
                            <label for="email">ইমেইল এড্রেস <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group">
                            <label for="password">পাসওয়ার্ড <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div></div>
                        <div class="col-md-6"><div class="form-group">
                            <label for="password_confirmation">কনফার্ম পাসওয়ার্ড <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                        </div></div>
                    </div>
                    <div class="form-group">
                        <label for="role">ভূমিকা (Role) <span class="text-danger">*</span></label>
                        <select class="form-control @error('role') is-invalid @enderror" name="role" required>
                            <option value="">একটি ভূমিকা নির্বাচন করুন</option>
                            <option value="1" {{ old('role') == '1' ? 'selected' : '' }}>এডমিন</option>
                            <option value="0" {{ old('role') == '0' ? 'selected' : '' }}>নরমাল এসআর</option>
                            <option value="2" {{ old('role') == '2' ? 'selected' : '' }}>ফুল এসআর</option>
                            <option value="3" {{ old('role') == '3' ? 'selected' : '' }}>কার্ড চেকার</option>
                            <option value="10" {{ old('role') == '10' ? 'selected' : '' }}>খোলা পণ্য</option>
                        </select>
                        @error('role') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> একাউন্ট তৈরি করুন</button>
                    <a class="btn btn-secondary" data-toggle="collapse" href="#addAccountCard" role="button">বাতিল</a>
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">{!! session('success') !!}<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">{!! session('error') !!}<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
    @endif
    {{-- Update form validation errors will be shown in the modal --}}

    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title">সকল ব্যবহারকারীর তালিকা</h3>
            <div class="card-tools">
                 <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" id="searchInput" class="form-control float-right" placeholder="সার্চ করুন...">
                    <div class="input-group-append"><button type="button" class="btn btn-default"><i class="fas fa-search"></i></button></div>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>আইডি</th>
                        <th>নাম</th>
                        <th>ইমেইল</th>
                        <th>ভূমিকা</th>
                        <th class="text-center">অ্যাকশন</th>
                    </tr>
                </thead> 
                <tbody id="userTableBody">
                    @forelse($users as $user)
                        <tr>
                            
                            <td>{{ $loop->iteration + ($users->currentPage()-1) * $users->perPage() }}</td>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @php
                                    $roles = [1 => 'এডমিন', 0 => 'নরমাল এসআর', 2 => 'ফুল এসআর', 3 => 'কার্ড চেকার', 10 =>"খোলা পণ্য"]; 
                                    $badges = [1 => 'danger', 0 => 'info', 2 => 'primary', 3 => 'warning',10 =>"success"];
                                @endphp
                                <span class="badge badge-{{ $badges[$user->role] ?? 'secondary' }}">{{ $roles[$user->role] ?? 'অজানা' }}</span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info editBtn" data-toggle="modal" data-target="#editAccountModal" 
                                    data-id="{{ $user->id }}" data-name="{{ $user->name }}" 
                                    data-email="{{ $user->email }}" data-role="{{ $user->role }}" title="এডিট করুন">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('acount.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirmDelete(event)">
                                    @csrf
                                    @method('DELETE')
                                    <button disabled type="submit" class="btn btn-sm btn-danger" title="ডিলিট করুন"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">কোনো ব্যবহারকারী পাওয়া যায়নি</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="card-footer clearfix"><div class="d-flex justify-content-center">{!! $users->links() !!}</div></div>
        @endif
    </div>
</div>

<!-- Edit Account Modal -->
<div class="modal fade" id="editAccountModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">একাউন্টের তথ্য আপডেট করুন</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                {{-- ভ্যালিডেশন এরর দেখানোর জন্য --}}
                <div id="edit-errors" class="alert alert-danger" style="display: none;"></div>
                
                <form id="editAccountForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="edit_name">সম্পূর্ণ নাম <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email">ইমেইল এড্রেস <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_role">ভূমিকা (Role) <span class="text-danger">*</span></label>
                        <select class="form-control" name="role" id="edit_role" required>
                            <option value="1">এডমিন</option>
                            <option value="0">নরমাল এসআর</option>
                            <option value="2">ফুল এসআর</option>
                            <option value="3">কার্ড চেকার</option>
                            <option value="10">খোলা পণ্য</option> 
                        </select>
                    </div>
                    <hr>
                    <p class="text-muted small">পাসওয়ার্ড পরিবর্তন করতে চাইলে নিচের ফিল্ডগুলো পূরণ করুন, অন্যথায় খালি রাখুন।</p>
                    <div class="form-group">
                        <label for="edit_password">নতুন পাসওয়ার্ড</label>
                        <input type="password" name="password" id="edit_password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_password_confirmation">কনফার্ম নতুন পাসওয়ার্ড</label>
                        <input type="password" name="password_confirmation" id="edit_password_confirmation" class="form-control">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">বাতিল</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> আপডেট করুন</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@stop
 
@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // লাইভ সার্চ ফাংশন
    $("#searchInput").on("keyup", function() {
        let value = $(this).val().toLowerCase();
        $("#userTableBody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // এডিট মডালে ডেটা পাস করার জন্য
    $('#editAccountModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        
        // ফর্মের action অ্যাট্রিবিউট আপডেট করা হচ্ছে
        var id = button.data('id');
        var action = "{{ url('acount') }}/" + id;
        modal.find('#editAccountForm').attr('action', action);
        
        // ইনপুট ফিল্ডগুলোতে ডেটা বসানো হচ্ছে
        modal.find('#edit_name').val(button.data('name'));
        modal.find('#edit_email').val(button.data('email'));
        modal.find('#edit_role').val(button.data('role'));
        
        // পাসওয়ার্ড ফিল্ডগুলো খালি করা হচ্ছে
        modal.find('#edit_password').val('');
        modal.find('#edit_password_confirmation').val('');
        // আগের এরর মেসেজ লুকিয়ে রাখা হচ্ছে
        $('#edit-errors').hide().html('');
    });

    // ডিলিট করার জন্য SweetAlert কনফার্মেশন
    function confirmDelete(event) {
        event.preventDefault();
        Swal.fire({
            title: 'আপনি কি নিশ্চিত?',
            text: "আপনি এই একাউন্টটি ডিলিট করতে চান!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'হ্যাঁ, ডিলিট করুন!',
            cancelButtonText: 'না, বাতিল করুন'
        }).then((result) => {
            if (result.isConfirmed) {
                event.target.submit();
            }
        });
        return false;
    }
    // ফাংশনটিকে গ্লোবাল স্কোপে আনা হচ্ছে যাতে onsubmit থেকে কল করা যায়
    window.confirmDelete = confirmDelete;

    // যদি আপডেট করার সময় ভ্যালিডেশন এরর থাকে, তাহলে মডালটি আবার খোলা হবে
    @if ($errors->any() && session('edit_error_id'))
        var userId = "{{ session('edit_error_id') }}";
        var button = $('button[data-id="' + userId + '"]');
        if (button.length) {
            // এরর মেসেজগুলো মডালে দেখানো হচ্ছে
            var errorsHtml = '<ul>';
            @foreach ($errors->all() as $error)
                errorsHtml += '<li>{{ $error }}</li>';
            @endforeach
            errorsHtml += '</ul>';
            $('#edit-errors').html(errorsHtml).show();

            // মডাল খোলা হচ্ছে
            $('#editAccountModal').modal('show');
            
            // পুরোনো ইনপুট ভ্যালুগুলো বসানো হচ্ছে
            $('#editAccountForm').attr('action', "{{ url('acount') }}/" + userId);
            $('#edit_name').val("{{ old('name') }}");
            $('#edit_email').val("{{ old('email') }}");
            $('#edit_role').val("{{ old('role') }}");
        }
    @endif
});
</script> 
@stop