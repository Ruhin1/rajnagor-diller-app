@extends('adminlte::page')

@section('title', 'ড্যাশবোর্ড')

@section('content_header')
    <div class="row align-items-center">
        <div class="col">
            <h1 class="m-0"><i class="fas fa-tachometer-alt"></i> ড্যাশবোর্ড</h1>
        </div>
        <div class="col-auto">
            <small class="text-muted">সর্বশেষ আপডেট: {{ now()->format('d M, Y h:i A') }}</small>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">



    {{-- রিপোর্ট ডাউনলোড সেকশন --}}
    <div class="row mb-4">
        <div class="col-lg-6 col-md-12 mb-3">
            <div class="card report-card sales-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-invoice-dollar"></i> বিক্রয় রিপোর্ট ডাউনলোড
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label for="sales_from_date" class="form-label">শুরুর তারিখ</label>
                            <input type="date" id="sales_from_date" class="form-control" value="{{ request('from') }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="sales_to_date" class="form-label">শেষ তারিখ</label>
                            <input type="date" id="sales_to_date" class="form-control" value="{{ request('to') }}">
                        </div>
                    </div> 
                    <hr>
                    <div class="download-buttons">
                        <a id="sales_pdf_link" href="{{ route('report.sales.pdf', ['from' => request('from'), 'to' => request('to'), 's' => 1]) }}" 
                           class="btn btn-danger btn-lg">
                            <i class="fas fa-file-pdf"></i> PDF ডাউনলোড
                        </a>
                        <a id="sales_excel_link" href="{{ route('sales.export', ['from' => request('from'), 'to' => request('to'), 's' => 1]) }}" 
                           class="btn btn-success btn-lg">
                            <i class="fas fa-file-excel"></i> Excel ডাউনলোড 
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-12 mb-3">
            <div class="card report-card orders-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shopping-cart"></i> অর্ডার রিপোর্ট ডাউনলোড
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label for="orders_from_date" class="form-label">শুরুর তারিখ</label>
                            <input type="date" id="orders_from_date" class="form-control" value="{{ request('from') }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="orders_to_date" class="form-label">শেষ তারিখ</label>
                            <input type="date" id="orders_to_date" class="form-control" value="{{ request('to') }}">
                        </div>
                    </div>
                    <hr>
                    <div class="download-buttons">
                        <a id="orders_pdf_link" href="{{ route('report.sales.pdf', ['from' => request('from'), 'to' => request('to'), 's' => 0]) }}" 
                           class="btn btn-danger btn-lg">
                            <i class="fas fa-file-pdf"></i> PDF ডাউনলোড
                        </a>
                        <a id="orders_excel_link" href="{{ route('sales.export', ['from' => request('from'), 'to' => request('to'), 's' => 0]) }}" 
                           class="btn btn-success btn-lg">
                            <i class="fas fa-file-excel"></i> Excel ডাউনলোড
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- অর্ডার সারাংশ --}}
    <div class="section-header">
        <h3><i class="fas fa-shopping-cart"></i> অর্ডার সারাংশ</h3>
    </div>
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>৳{{ number_format($today_order, 2) }}</h3>
                    <p>আজকের অর্ডার</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-day"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>৳{{ number_format($weekly_order, 2) }}</h3>
                    <p>সাপ্তাহিক অর্ডার</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-week"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>৳{{ number_format($monthly_order, 2) }}</h3>
                    <p>মাসিক অর্ডার</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-alt"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>৳{{ number_format($all_order, 2) }}</h3>
                    <p>সর্বোমোট অর্ডার</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-alt"></i></div>
            </div>
        </div>
    </div>

    {{-- বিক্রয় সারাংশ --}}
    <div class="section-header">
        <h3><i class="fas fa-chart-line"></i> বিক্রয় সারাংশ</h3>
    </div>
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>৳{{ number_format($today_sales, 2) }}</h3>
                    <p>আজকের বিক্রয়</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>৳{{ number_format($weekly_sales, 2) }}</h3>
                    <p>সাপ্তাহিক বিক্রয়</p>
                </div>
                <div class="icon"><i class="fas fa-chart-area"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>৳{{ number_format($monthly_sales, 2) }}</h3>
                    <p>মাসিক বিক্রয়</p>
                </div>
                <div class="icon"><i class="fas fa-chart-pie"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>৳{{ number_format($all_sales, 2) }}</h3>
                    <p>সর্বোমোট বিক্রয়</p>
                </div>
                <div class="icon"><i class="fas fa-chart-pie"></i></div>
            </div>
        </div>
    </div>

    {{-- খরছের সারাংশ --}}
    <div class="section-header">
        <h3><i class="fas fa-file-invoice-dollar text-danger mr-2"></i> খরছের সারাংশ</h3>
    </div>
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>৳{{ number_format($today_expense, 2) }}</h3>
                    <p>আজকের খরচ</p>
                </div>
                <div class="icon"><i class="fas fa-wallet"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>৳{{ number_format($weekly_expense, 2) }}</h3>
                    <p>সাপ্তাহিক খরচ</p>
                </div>
                <div class="icon"><i class="fas fa-wallet"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>৳{{ number_format($month_expense, 2) }}</h3>
                    <p>মাসিক খরচ</p>
                </div>
                <div class="icon"><i class="fas fa-wallet"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>৳{{ number_format($total_expense, 2) }}</h3>
                    <p>সর্বোমোট খরচ</p>
                </div>
                <div class="icon"><i class="fas fa-wallet"></i></div>
            </div>
        </div>
    </div>
    {{-- খরছ বাদ দিয়ে লাভের সারাংশ --}}
    <div class="section-header">
        <h3><i class="fas fa-chart-line text-success mr-2"></i> খরছ বাদ দিয়ে লাভের সারাংশ</h3>
    </div>
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>৳{{ number_format($today_profit, 2) }}</h3>
                    <p>আজকের লাভ</p>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>৳{{ number_format($weekly_profit, 2) }}</h3>
                    <p>সাপ্তাহিক লাভ</p>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>৳{{ number_format($monthly_profit, 2) }}</h3>
                    <p>মাসিক লাভ</p>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>৳{{ number_format($total_profit, 2) }}</h3>
                    <p>সর্বোমোট লাভ</p>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>
        
    </div>
    {{-- অন্যন্য সারাংশ --}}
    <div class="section-header">
        <h3><i class="fas fa-ellipsis-h text-info mr-2"></i> অন্যন্য সারাংশ</h3>
    </div>
    <div class="row">
    <!-- মোট কাস্টমার -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $total_customers }}</h3>
                <p>মোট কাস্টমার</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
        </div>
    </div>

    <!-- মোট ডিলার -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $total_dealers }}</h3>
                <p>মোট ডিলার</p>
            </div>
            <div class="icon"><i class="fas fa-handshake"></i></div>
        </div>
    </div>

    <!-- মোট প্রোডাক্ট -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="small-box bg-purple">
            <div class="inner">
                <h3>{{ $total_products }}</h3>
                <p>মোট প্রোডাক্ট</p>
            </div>
            <div class="icon"><i class="fas fa-cubes"></i></div>
        </div>
    </div>

    <!-- মোট বাবুর্চি -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="small-box bg-orange">
            <div class="inner">
                <h3>{{ $total_chefs }}</h3>
                <p>মোট বাবুর্চি</p>
            </div>
            <div class="icon"><i class="fas fa-hat-chef"></i></div>
        </div>
    </div>

    <!-- মোট ব্যবহারকারী -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="small-box bg-teal">
            <div class="inner">
                <h3>{{ $total_users }}</h3>
                <p>মোট ব্যবহারকারী</p>
            </div>
            <div class="icon"><i class="fas fa-users-cog"></i></div>
        </div>
    </div>

    <!-- মোট বকেয়া -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ number_format($total_due, 2) }}</h3>
                <p>মোট বকেয়া</p>
            </div>
            <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
        </div>
    </div>

    <!-- কাস্টমার বকেয়া -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="small-box bg-pink">
            <div class="inner">
                <h3>{{ number_format($customer_due, 2) }}</h3>
                <p>কাস্টমার বকেয়া</p>
            </div>
            <div class="icon"><i class="fas fa-user-tag"></i></div>
        </div>
    </div>

    <!-- ডিলার বকেয়া -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="small-box bg-maroon">
            <div class="inner">
                <h3>{{ number_format($dealer_due, 2) }}</h3>
                <p>ডিলার বকেয়া</p>
            </div>
            <div class="icon"><i class="fas fa-briefcase"></i></div>
        </div>
    </div>
</div>

    {{-- চার্ট সেকশন --}}
    <div class="section-header">
        <h3><i class="fas fa-chart-bar"></i> বিশ্লেষণ চার্ট</h3>
    </div>
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0"><i class="fas fa-users"></i> কাস্টমার বৃদ্ধি</h5>
                </div>
                <div class="card-body">
                    <canvas id="customerGrowthChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0"><i class="fas fa-users"></i> ডিলার বৃদ্ধি</h5>
                </div>
                <div class="card-body">
                    <canvas id="dealerGrowthChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-calendar-alt"></i> মাসিক বিক্রয় ট্রেন্ড</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlySalesChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-line"></i> মাসভিত্তিক বিক্রয়</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-box"></i> শীর্ষ ৫ প্রোডাক্ট</h5>
                </div>
                <div class="card-body">
                    <canvas id="topProductsChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- টেবিল সেকশন --}}
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-history"></i> সর্বশেষ অর্ডার</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>ইনভয়েস</th>
                                    <th>কাস্টমার</th>
                                    <th class="text-right">মোট</th>
                                    <th class="text-right">পেইড</th>
                                    <th>তারিখ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latest_orders as $sale)
                                <tr>
                                    <td><span class="badge badge-primary">#{{ $sale->id }}</span></td>
                                    <td>
                                        @if($sale->customer_id)
                                            {{ $sale->customer->name }}
                                        @elseif($sale->dealer_id)
                                            {{ $sale->dealer->name }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-right"><strong>৳{{ number_format($sale->total, 2) }}</strong></td>
                                    <td class="text-right">৳{{ number_format($sale->paid, 2) }}</td>
                                    <td>{{ $sale->created_at->format('d M, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">কোনো বিক্রয় পাওয়া যায়নি</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-history"></i> সর্বশেষ বিক্রয়</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>ইনভয়েস</th>
                                    <th>কাস্টমার</th>
                                    <th class="text-right">মোট</th>
                                    <th class="text-right">পেইড</th>
                                    <th>তারিখ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latest_sales as $sale)
                                <tr>
                                    <td><span class="badge badge-primary">#{{ $sale->id }}</span></td>
                                    <td>
                                        @if($sale->customer_id)
                                            {{ $sale->customer->name }}
                                        @elseif($sale->dealer_id)
                                            {{ $sale->dealer->name }}
                                        @else
                                            {{ $sale->guest_name }}
                                        @endif
                                    </td>
                                    <td class="text-right"><strong>৳{{ number_format($sale->total, 2) }}</strong></td>
                                    <td class="text-right">৳{{ number_format($sale->paid, 2) }}</td>
                                    <td>{{ $sale->created_at->format('d M, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">কোনো বিক্রয় পাওয়া যায়নি</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0"><i class="fas fa-user-plus"></i> সর্বশেষ কাস্টমার</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>নাম</th>
                                    <th>মোবাইল</th>
                                    <th>যোগদানের তারিখ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latest_customers as $customer)
                                <tr>
                                    <td>{{ $customer->name }}</td>
                                    <td>{{ $customer->number }}</td>
                                    <td>{{ $customer->created_at->format('d M, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">কোনো কাস্টমার পাওয়া যায়নি</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4"> 
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0"><i class="fas fa-user-plus"></i> সর্বশেষ ডিলার</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>নাম</th>
                                    <th>মোবাইল</th>
                                    <th>যোগদানের তারিখ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latest_dealers as $customer)
                                <tr>
                                    <td>{{ $customer->name }}</td>
                                    <td>{{ $customer->phone }}</td>
                                    <td>{{ $customer->created_at->format('d M, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">কোনো কাস্টমার পাওয়া যায়নি</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- স্টক সতর্কতা --}}
    @if($low_stock_products->isNotEmpty())
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle"></i> স্টক সতর্কতা (৫০ এর নিচে)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>প্রোডাক্ট</th>
                                    <th class="text-center">বর্তমান স্টক</th>
                                    <th class="text-center">স্ট্যাটাস</th>
                                   
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($low_stock_products as $product)
                                <tr>
                                    <td><strong>{{ $product->name }}</strong></td>
                                    <td class="text-center">
                                        <span class="badge {{ $product->quantity < 20 ? 'badge-danger' : 'badge-warning' }}">
                                            {{ $product->quantity }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($product->quantity < 20)
                                            <span class="text-danger"><i class="fas fa-times-circle"></i> জরুরি</span>
                                        @else
                                            <span class="text-warning"><i class="fas fa-exclamation-circle"></i> সতর্কতা</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif 

    {{-- মালিকদের লাভ বণ্টন --}}
<div class="section-header">
    <h3><i class="fas fa-chart-pie text-primary mr-2"></i> খরচ বাদ দিয়ে লাভের সারাংশ</h3>
</div>

<div class="row mb-3">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="small-box bg-gradient-success">
            <div class="inner">
                <h3>৳{{ number_format($today_profit_actual, 2) }}</h3>
                <p>আজকের প্রকৃত লাভ</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-day"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="small-box bg-gradient-info">
            <div class="inner">
                <h3>৳{{ number_format($month_profit_actual, 2) }}</h3>
                <p>এই মাসের প্রকৃত লাভ</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-alt"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="small-box bg-gradient-warning">
            <div class="inner">
                <h3>৳{{ number_format($year_profit_actual, 2) }}</h3>
                <p>এই বছরের প্রকৃত লাভ</p>
            </div>
            <div class="icon"><i class="fas fa-chart-bar"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="small-box bg-gradient-danger">
            <div class="inner">
                <h3>৳{{ number_format($all_time_profit_actual, 2) }}</h3>
                <p>সর্বমোট প্রকৃত লাভ</p>
            </div>
            <div class="icon"><i class="fas fa-landmark"></i></div>
        </div>
    </div>
</div>

<hr>
<h2 class="text-center my-4 font-weight-bold">মালিক অনুযায়ী লাভ বণ্টন</h2>

<div class="row">
    @forelse($owners as $owner)
    <div class="col-lg-6 col-xl-4 mb-4">
        <div class="owner-card">
            <div class="owner-card-header">
                <div class="owner-info">
                    <i class="fas fa-user-tie owner-icon"></i>
                    <div>
                        <h5 class="owner-name mb-0">{{ $owner->name }}</h5>
                        <small class="text-muted">শেয়ার: {{ $owner->share_percentage }}%</small>
                    </div>
                </div>
                <span class="share-badge">{{ $owner->share_percentage }}%</span>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush profit-list">
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-calendar-day text-success"></i> আজকের লাভ</span>
                            <strong class="text-success">৳{{ number_format(($today_profit_actual * $owner->share_percentage) / 100, 2) }}</strong>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-calendar-alt text-info"></i> মাসিক লাভ</span>
                            <strong class="text-info">৳{{ number_format(($month_profit_actual * $owner->share_percentage) / 100, 2) }}</strong>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-chart-bar text-warning"></i> বাৎসরিক লাভ</span>
                            <strong class="text-warning">৳{{ number_format(($year_profit_actual * $owner->share_percentage) / 100, 2) }}</strong>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-landmark text-danger"></i> সর্বমোট লাভ</span>
                            <strong class="text-danger">৳{{ number_format(($all_time_profit_actual * $owner->share_percentage) / 100, 2) }}</strong>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-warning text-center">
            <i class="fas fa-info-circle"></i> কোনো মালিকের তথ্য পাওয়া যায়নি।
        </div>
    </div>
    @endforelse
</div>

</div>
@stop

@section('css')
{!! ToastMagic::styles() !!}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    /* সেকশন হেডার */
    .section-header {
        margin: 2rem 0 1.5rem 0;
        padding-bottom: 0.5rem;
        border-bottom: 3px solid #007bff;
    }
    .section-header h3 {
        color: #333;
        font-weight: 600;
        margin: 0;
    }

    /* রিপোর্ট কার্ড */
    .report-card {
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: none;
        border-top: 4px solid;
        overflow: hidden;
    }
    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }
    .sales-card { border-top-color: #28a745; }
    .orders-card { border-top-color: #17a2b8; }

    .report-card .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #dee2e6;
        padding: 1rem 1.25rem;
    }
    .report-card .card-body {
        padding: 1.5rem;
    }

    /* ডাউনলোড বাটন */
    .download-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }
    .download-buttons .btn {
        flex: 1;
        min-width: 180px;
        max-width: 220px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .download-buttons .btn:hover {
        transform: scale(1.05);
    }

    /* Small Box Customization */
    .small-box {
        border-radius: 10px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    .small-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }
    .small-box .icon {
        font-size: 70px;
        opacity: 0.3;
    }

    /* কার্ড স্টাইল */
    .card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        border: none;
        transition: all 0.3s ease;
    }
    .card:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
    }
    .card-header {
        border-radius: 10px 10px 0 0 !important;
        font-weight: 600;
    }

    /* টেবিল স্টাইল */
    .table-responsive {
        border-radius: 0 0 10px 10px;
    }
    .table thead th {
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* মালিক কার্ড */
    .owner-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        overflow: hidden;
        border: 1px solid #e3e6f0;
    }
    .owner-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
    .owner-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .owner-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .owner-icon {
        font-size: 2.5rem;
        opacity: 0.9;
    }
    .owner-name {
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
    }
    .share-badge {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-size: 1rem;
        font-weight: 600;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }
    .profit-list {
        border: none;
    }
    .profit-list .list-group-item {
        border: none;
        padding: 1rem 1.25rem;
        transition: background 0.2s ease;
    }
    .profit-list .list-group-item:hover {
        background-color: #f8f9fa;
    }
    .profit-list .list-group-item:not(:last-child) {
        border-bottom: 1px solid #e9ecef;
    }

    /* ব্যাজ স্টাইল */
    .badge {
        padding: 0.4em 0.8em;
        font-weight: 500;
    }

    /* রেসপন্সিভ ডিজাইন */
    @media (max-width: 768px) {
        .section-header h3 {
            font-size: 1.5rem;
        }
        .owner-card-header {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }
        .share-badge {
            align-self: flex-end;
        }
        .download-buttons .btn {
            min-width: 100%;
        }
    }

    /* অ্যানিমেশন */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .card, .small-box, .owner-card {
        animation: fadeInUp 0.5s ease;
    }

    /* চার্ট কন্টেইনার */
    canvas {
        max-height: 350px;
    }

    /* স্ক্রোলবার স্টাইল */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
{!! ToastMagic::scripts() !!}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // চার্ট কনফিগারেশন
    Chart.defaults.font.family = 'Arial, sans-serif';
    Chart.defaults.color = '#666';

    // মাসভিত্তিক বিক্রয় বার চার্ট
    new Chart(document.getElementById('salesChart'), {
        type: 'bar',
        data: {
            labels: @json($months),
            datasets: [{
                label: 'বিক্রয় (৳)',
                data: @json($sales_data),
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 12,
                    titleFont: { size: 14 },
                    bodyFont: { size: 13 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '৳' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    // ডিলার গ্রোথ লাইন চার্ট
    new Chart(document.getElementById('dealerGrowthChart'), {
        type: 'line',
        data: {
            labels: @json($dealer_growth->keys()),
            datasets: [{
                label: 'নতুন ডিলার',
                data: @json($dealer_growth->values()),
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 5,
                pointBackgroundColor: '#ffc107',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // কাস্টমার গ্রোথ লাইন চার্ট
    new Chart(document.getElementById('customerGrowthChart'), {
        type: 'line',
        data: {
            labels: @json($customer_growth->keys()),
            datasets: [{
                label: 'নতুন কাস্টমার',
                data: @json($customer_growth->values()),
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 5,
                pointBackgroundColor: '#28a745',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // শীর্ষ প্রোডাক্ট হরাইজন্টাল বার চার্ট
    new Chart(document.getElementById('topProductsChart'), {
        type: 'bar',
        data: {
            labels: @json($top_products->keys()),
            datasets: [{
                label: 'বিক্রিত পরিমাণ',
                data: @json($top_products->values()),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)'
                ],
                borderWidth: 2,
                borderRadius: 5
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { beginAtZero: true }
            }
        }
    });

    // মাসিক সেলস ট্রেন্ড লাইন চার্ট
    new Chart(document.getElementById('monthlySalesChart'), {
        type: 'line',
        data: {
            labels: @json($monthly_sales_chart->keys()),
            datasets: [{
                label: 'মাসিক বিক্রয়',
                data: @json($monthly_sales_chart->values()),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 6,
                pointBackgroundColor: '#007bff',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'বিক্রয়: ৳' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '৳' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // রিপোর্ট ডাউনলোড লিংক আপডেট ফাংশন
    function updateDownloadLinks(fromId, toId, pdfLinkId, excelLinkId) {
        const fromDate = $(fromId).val();
        const toDate = $(toId).val();

        if (!fromDate || !toDate) return;

        // তারিখ ভ্যালিডেশন
        if (new Date(fromDate) > new Date(toDate)) {
            Swal.fire({
                icon: 'error',
                title: 'ভুল তারিখ',
                text: 'শুরুর তারিখ শেষ তারিখের চেয়ে বড় হতে পারবে না',
                confirmButtonText: 'ঠিক আছে'
            });
            return;
        }

        const pdfLink = $(pdfLinkId);
        const excelLink = $(excelLinkId);

        const pdfBaseUrl = pdfLink.attr('href').split('?')[0];
        const excelBaseUrl = excelLink.attr('href').split('?')[0];

        const urlParams = new URLSearchParams(pdfLink.attr('href').split('?')[1]);
        const sParam = urlParams.get('s');

        const newQueryString = `?from=${fromDate}&to=${toDate}&s=${sParam}`;

        pdfLink.attr('href', pdfBaseUrl + newQueryString);
        excelLink.attr('href', excelBaseUrl + newQueryString);
    }

    // বিক্রয় রিপোর্ট তারিখ পরিবর্তন
    $('#sales_from_date, #sales_to_date').on('change', function() {
        updateDownloadLinks('#sales_from_date', '#sales_to_date', '#sales_pdf_link', '#sales_excel_link');
    });

    // অর্ডার রিপোর্ট তারিখ পরিবর্তন
    $('#orders_from_date, #orders_to_date').on('change', function() {
        updateDownloadLinks('#orders_from_date', '#orders_to_date', '#orders_pdf_link', '#orders_excel_link');
    });

    // স্টক সতর্কতা অ্যানিমেশন
    $('.low-stock-alert').each(function(index) {
        $(this).css({
            'animation-delay': (index * 0.1) + 's'
        });
    });

    // পেজ লোড হওয়ার সময় স্মুথ স্ক্রল
    $('html, body').animate({ scrollTop: 0 }, 'slow');
});
</script>
@stop 