@extends('adminlte::page')

@section('title', 'স্টক মুভমেন্ট লগ')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0" style="color: #007bff;">স্টক মুভমেন্ট লগ</h1>
    </div>
@stop

@section('content')

<div class="card shadow-sm border-0 sm-card">

    {{-- ফিল্টার বার — প্রোডাক্টের নাম ও ধরন অনুযায়ী খোঁজা যাবে (ঐচ্ছিক) --}}
    <div class="card-header bg-white sm-card-header">
        <form method="GET" action="{{ url()->current() }}" class="sm-filter-form">
            <div class="sm-filter-row">
                <input type="text" name="product" value="{{ request('product') }}"
                       class="form-control form-control-sm sm-filter-input"
                       placeholder="প্রোডাক্টের নাম দিয়ে খুঁজুন...">

                <select name="type" class="form-control form-control-sm sm-filter-select">
                    <option value="">সব ধরন</option>
                    <option value="in"  {{ request('type') == 'in'  ? 'selected' : '' }}>স্টক ইন</option>
                    <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>স্টক আউট</option>
                </select>

                <button type="submit" class="btn btn-primary btn-sm sm-filter-btn">
                    <i class="fas fa-search"></i> খুঁজুন
                </button>

                @if(request('product') || request('type'))
                    <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-sm sm-filter-btn">
                        <i class="fas fa-times"></i> রিসেট
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="card-body p-0">

        <div class="sm-table-wrap">
            <table class="table table-striped table-hover mb-0 sm-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width:4%">#</th>
                        <th style="min-width:160px">প্রোডাক্ট</th>
                        <th style="min-width:100px">ধরন</th>
                        <th class="text-center" style="min-width:80px">পরিমাণ</th>
                        <th class="text-center" style="min-width:90px">আগের স্টক</th>
                        <th class="text-center" style="min-width:90px">নতুন স্টক</th>
                        <th style="min-width:110px">করেছেন</th>
                        <th style="min-width:150px">নোট</th>
                        <th style="min-width:150px">তারিখ ও সময়</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $index => $m)
                        <tr>
                            <td class="text-center" data-label="#">
                                {{ $movements->firstItem() + $index }}
                            </td>
                            <td data-label="প্রোডাক্ট">
                                <span class="fw-bold">{{ $m->product->name ?? 'N/A' }}</span>
                            </td>
                            <td data-label="ধরন">
                                @if($m->type === 'in')
                                    <span class="badge badge-success sm-badge">
                                        <i class="fas fa-arrow-down mr-1"></i> স্টক ইন
                                    </span>
                                @else
                                    <span class="badge badge-danger sm-badge">
                                        <i class="fas fa-arrow-up mr-1"></i> স্টক আউট
                                    </span>
                                @endif
                            </td>
                            <td class="text-center font-weight-bold" data-label="পরিমাণ">
                                {{ $m->quantity }}
                            </td>
                            <td class="text-center text-muted" data-label="আগের স্টক">
                                {{ $m->old_stock }}
                            </td>
                            <td class="text-center font-weight-bold" data-label="নতুন স্টক">
                                {{ $m->new_stock }}
                            </td>
                            <td data-label="করেছেন">
                                {{ $m->creator->name ?? 'N/A' }}
                            </td>
                            <td data-label="নোট">
                                {{ $m->note ?? '-' }}
                            </td>
                            <td class="text-muted" data-label="তারিখ ও সময়">
                                {{ $m->created_at->format('d M Y, h:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                কোনো স্টক মুভমেন্ট রেকর্ড পাওয়া যায়নি।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    @if($movements->hasPages())
        <div class="card-footer bg-white d-flex flex-column flex-sm-row justify-content-between align-items-center sm-pagination-footer">
            <small class="text-muted mb-2 mb-sm-0">
                মোট {{ $movements->total() }} টি রেকর্ডের মধ্যে
                {{ $movements->firstItem() }}–{{ $movements->lastItem() }} দেখানো হচ্ছে
            </small>
            {{ $movements->links() }}
        </div>
    @endif

</div>

@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* ══════════════════════════════════════════════════════════
           কার্ড ও ফিল্টার বার
        ══════════════════════════════════════════════════════════ */
        .sm-card { border-radius: 10px; overflow: hidden; }
        .sm-card-header { padding: 10px 14px; }

        .sm-filter-form { width: 100%; }
        .sm-filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .sm-filter-input { flex: 1 1 220px; min-width: 160px; }
        .sm-filter-select { flex: 0 1 140px; min-width: 120px; }
        .sm-filter-btn { flex: 0 0 auto; white-space: nowrap; }

        /* ══════════════════════════════════════════════════════════
           টেবিল — ডেস্কটপ/ট্যাবলেটে স্বাভাবিক হরাইজন্টাল-স্ক্রল টেবিল
        ══════════════════════════════════════════════════════════ */
        .sm-table-wrap { overflow-x: auto; width: 100%; }
        .sm-table { font-size: 13.5px; white-space: nowrap; }
        .sm-table thead th {
            background: #343a40;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
            padding: 10px 12px;
            border: none;
            white-space: nowrap;
        }
        .sm-table tbody td {
            padding: 9px 12px;
            vertical-align: middle;
        }
        .sm-badge { font-size: 11.5px; font-weight: 600; padding: 5px 9px; }

        .sm-pagination-footer { padding: 10px 14px; }
        .sm-pagination-footer .pagination { margin-bottom: 0; }

        /* ══════════════════════════════════════════════════════════
           মোবাইল / ছোট স্ক্রিন (≤ 767px) — টেবিলের বদলে প্রতিটা রো
           একটা কার্ডের মতো স্ট্যাক হয়ে দেখাবে, thead লুকানো থাকবে,
           প্রতিটা সেলের আগে data-label থেকে লেবেল বসবে — ফলে ছোট
           স্ক্রিনেও সাইডওয়েজ স্ক্রল না করে সব তথ্য পড়া যাবে।
        ══════════════════════════════════════════════════════════ */
        @media (max-width: 767px) {
            .sm-table-wrap { overflow-x: visible; }

            .sm-table thead { display: none; }

            .sm-table, .sm-table tbody, .sm-table tr, .sm-table td {
                display: block;
                width: 100%;
            }

            .sm-table tr {
                margin-bottom: 10px;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                padding: 6px 0;
                background: #fff;
            }

            .sm-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                text-align: right;
                white-space: normal;
                padding: 7px 12px;
                border: none !important;
                border-bottom: 1px solid #f1f1f1 !important;
            }
            .sm-table td:last-child { border-bottom: none !important; }

            .sm-table td::before {
                content: attr(data-label);
                font-weight: 700;
                font-size: 11.5px;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: .3px;
                text-align: left;
                padding-right: 10px;
            }

            /* খালি-রেজাল্ট রো-টা এই স্ট্যাকিং থেকে বাদ রাখা হলো */
            .sm-table tr td[colspan] {
                display: block;
                text-align: center;
            }
            .sm-table tr td[colspan]::before { content: none; }

            .sm-filter-row { flex-direction: column; }
            .sm-filter-input, .sm-filter-select, .sm-filter-btn { width: 100%; flex: 1 1 auto; }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stop