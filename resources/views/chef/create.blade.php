@extends('adminlte::page')

@section('title', 'বাবুর্চি ম্যানেজমেন্ট')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">বাবুর্চি ম্যানেজমেন্ট</h1>
        {{-- এই বাটনটি কলাপসিবল কার্ড খুলবে --}}
        <a class="btn btn-primary" data-toggle="collapse" href="#addChefCard" role="button" aria-expanded="false" aria-controls="addChefCard">
            <i class="fas fa-plus-circle mr-1"></i> নতুন বাবুর্চি যোগ করুন
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    {{-- নতুন বাবুর্চি যোগ করার কলাপসিবল কার্ড --}}
    <div class="collapse" id="addChefCard">
        <div class="card card-primary shadow-sm mb-4">
            <div class="card-header">
                <h3 class="card-title">নতুন বাবুর্চির তথ্য দিন</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('chef.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="c_number">বহি নং <span class="text-danger">*</span></label>
                                <input type="number" name="c_number" id="c_number" class="form-control" value="{{ old('c_number') }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">নাম <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="m_number">মোবাইল নাম্বার <span class="text-danger">*</span></label>
                                <input type="text" name="m_number" id="m_number" class="form-control" value="{{ old('m_number') }}" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> সেভ করুন</button>
                    <a class="btn btn-secondary" data-toggle="collapse" href="#addChefCard" role="button">বাতিল</a>
                </form>
            </div>
        </div>
    </div>

    {{-- সাকসেস এবং এরর মেসেজ দেখানোর জন্য --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {!! session('success') !!}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    {{-- বাবুর্চি তালিকা কার্ড --}}
    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title">সকল বাবুর্চির তালিকা</h3>
            <div class="card-tools">
                <form action="{{ route('chef.create') }}" method="GET" class="form-inline">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="সার্চ করুন..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-default" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>বহি নং</th>
                        <th>নাম</th>
                        <th>মোবাইল নং</th>
                        <th>কার্ড ব্যবহার</th>
                        <th class="text-center">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($chefs as $chef)
                        <tr>
                            <td>{{ $loop->iteration + ($chefs->currentPage()-1) * $chefs->perPage() }}</td>
                            <td>{{ $chef->c_number }}</td>
                            <td>{{ $chef->name }}</td>
                            <td>{{ $chef->m_number }}</td>
                            <td>{{ $chef->get_card_chefs_count }} টি কার্ড ব্যবহার করেছেন।</td>
                            <td class="text-center">
                                {{-- এডিট এবং ডিলিট বাটন --}}
                                <button class="btn btn-sm btn-info editBtn" data-toggle="modal" data-target="#editChefModal" 
                                    data-id="{{ $chef->id }}" data-c_number="{{ $chef->c_number }}" 
                                    data-name="{{ $chef->name }}" data-m_number="{{ $chef->m_number }}" title="এডিট করুন">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('chef.destroy', $chef->id) }}" method="POST" class="d-inline" onsubmit="return confirmDelete(event)">
                                    @csrf
                                    @method('DELETE')
                                    <button disabled type="submit" class="btn btn-sm btn-danger" title="ডিলিট করুন"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                @if(request('search'))
                                    "{{ request('search') }}" এর জন্য কোনো ফলাফল পাওয়া যায়নি।
                                @else
                                    কোনো রেকর্ড পাওয়া যায়নি।
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($chefs->hasPages())
        <div class="card-footer clearfix">
            <div class="d-flex justify-content-center">
                {!! $chefs->appends(request()->query())->links() !!}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Edit Chef Modal -->
<div class="modal fade" id="editChefModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">বাবুর্চির তথ্য আপডেট করুন</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editChefForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="edit_c_number">বহি নং <span class="text-danger">*</span></label>
                        <input type="number" name="c_number" id="edit_c_number" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_name">নাম <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_m_number">মোবাইল নাম্বার <span class="text-danger">*</span></label>
                        <input type="text" name="m_number" id="edit_m_number" class="form-control" required>
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
    // এডিট মডালে ডেটা পাস করার জন্য
    $('#editChefModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        
        // ফর্মের action অ্যাট্রিবিউট আপডেট করা হচ্ছে
        var id = button.data('id');
        var action = "{{ url('chef') }}/" + id;
        modal.find('#editChefForm').attr('action', action);
        
        // ইনপুট ফিল্ডগুলোতে ডেটা বসানো হচ্ছে
        modal.find('#edit_c_number').val(button.data('c_number'));
        modal.find('#edit_name').val(button.data('name'));
        modal.find('#edit_m_number').val(button.data('m_number'));
    });

    // ডিলিট করার জন্য SweetAlert কনফার্মেশন
    function confirmDelete(event) {
        event.preventDefault(); // ফর্ম সাবমিট বন্ধ করা
        Swal.fire({
            title: 'আপনি কি নিশ্চিত?',
            text: "আপনি এই বাবুর্চিকে ডিলিট করতে চান!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'হ্যাঁ, ডিলিট করুন!',
            cancelButtonText: 'না, বাতিল করুন'
        }).then((result) => {
            if (result.isConfirmed) {
                event.target.submit(); // কনফার্ম করলে ফর্ম সাবমিট হবে
            }
        });
        return false;
    }
</script> 
@stop