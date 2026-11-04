<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProductController,
    CustomerController,
    CostController,
    PosController,
    SaleController,
    MyDashboardController,
    ReportController,
    SaleReturnController,
    CardController,
    DealerController,
    ChefController,
    AcountController,
    PaymentController,
    MorichDailySaleController,
    MorichProductController,
    CustomLedgerController,
    BluetoothPrintController,
    
};   
use Illuminate\Http\Request;
use App\Models\Customer;



//Hello

// all route rap in auth middleware dsfdf
Route::middleware("auth","role.zero.only")->group(function () {
    //base route
    Route::get("/", function () {

        if(auth()->user()->role == 0){
            return redirect("/pos");
        }else if(auth()->user()->role == 2){
            return redirect("/pos");

        }else if(auth()->user()->role == 3){
            return redirect("/cards/check");    
        }else{
            return redirect("/my-dashboard");
        } 
    })->name("homes"); 

    // sales all route


    Route::get('/sales/new-orders', [SaleController::class, 'newOrders'])->name('sales.new_orders');
    Route::post('/sales/delivery/{id}', [SaleController::class, 'processDelivery'])->name('sales.delivery');
    Route::post('/sales/revert-to-new/{id}', [SaleController::class, 'revertToNewOrder'])->name('sales.revert_to_new');

    // sells all route
    Route::get("/sales/pending", [SaleController::class, "pending"])->name(
        "sales.pending"
    );
    // নতুন এই লাইনটি যুক্ত করুন
    Route::get('/sales/pending/pdf', [SaleController::class, 'downloadPendingPDF'])->name('sales.pending.pdf');

    Route::get("/sales/confrim", [SaleController::class, "confrim"])->name(
        "sales.confrim"
    );
    // নতুন এই লাইনটি যুক্ত করুন
    Route::get('/sales/confrim/pdf', [SaleController::class, 'downloadConfrimPDF'])->name('sales.confrim.pdf');
    Route::post("/sales/{id}/complete", [
        SaleController::class,
        "complete",
    ])->name("sales.complete");
    Route::get("/sales/{sale}/edit", [SaleController::class, "edit"])->name(
        "sales.edit"
    );
     Route::get("/delar/{sale}/edit", [SaleController::class, "delaredit"])->name(
        "delar.edit"
    );
    Route::put("/sales/{sale}", [SaleController::class, "update"])->name(
        "sales.update"
    );
    Route::put("/dsales/{sale}", [SaleController::class, "dupdate"])->name(
        "dsales.update"
    );
    Route::get("/sales/export", [
        ReportController::class,
        "exportSalesReport",
    ])->name("sales.export");
    Route::get("/report/sales/pdf", [
        ReportController::class,
        "exportPDF",
    ])->name("report.sales.pdf"); 

    Route::delete('/sales/{id}', [SaleController::class, 'destroy'])->name('sales.destroy');

    
    Route::get("/returns/update", [
        SaleReturnController::class,
        "searchupdate",
    ])->name("returns.search.update");

    Route::get("/returns/{sale}/create", [
        SaleReturnController::class,
        "create",
    ])->name("returns.create");

    Route::post("/returns/{sale}/store", [
        SaleReturnController::class,
        "store",
    ])->name("returns.store");
    Route::post("/sale-return", [
        SaleReturnController::class,
        "returnSale",
    ])->name("sale.return");

 
    // Deshboard route
    Route::get("/my-dashboard", [MyDashboardController::class, "index"])->name(
        "mydashboard"
    );

    Route::controller(CustomerController::class)->group(function () {
        Route::get("/customers", "index")->name('r.customerindex');
        Route::post("/customers/store", "store")->name("customers.store");
        Route::post("/customers/update/{id}", "update")->name("customers.update");
        Route::delete("/customersdelate/{id}", "destroy")->name("customers.destroy");
        // ===========================
        // Route::get("/dcustomers", "dindex")->name('dr.customerindex');
        // Route::post("/dcustomers/store", "dstore")->name("dcustomers.store");
        // Route::post("/dcustomers/update/{id}", "dupdate")->name("dcustomers.update");
        // Route::delete("/dcustomersdelate/{id}", "ddestroy")->name("dcustomers.destroy");

    });



    Route::get("/customers/search", [CustomerController::class, "search"]);
    Route::post('/customers/quick-add', [CustomerController::class, 'quickAdd'])->name('customers.quick.add');


    Route::get("/pos", [PosController::class, "index"])->name("pos.index");
    Route::get("/other-sale", [PosController::class, "otherSale"])->name("other.sale");

    // Ajax APIs
    Route::get("/pos/products", [PosController::class, "products"])->name('r.posproducts'); // ?q=
    Route::get("/pos/customers", [PosController::class, "customers"])->name('r.poscustomers'); // dropdown/search
    Route::post("/sales", [SaleController::class, "store"])->name(
        "sales.store"
    );

    Route::post("/isdipu", [SaleController::class, "isdipu"])->name(
        "sales.isdipu"
    );

    Route::get("/sales/{sale}", [SaleController::class, "show"])->name(
        "sales.show"
    ); // invoice



    Route::get("/dealer/{sale}", [SaleController::class, "dealershow"])->name(
        "dealer.show"
    ); // invoice

    Route::controller(ProductController::class)->group(function () {
        Route::get("/products4", "index")->name('products.index');
        Route::post("/products/store", "store")->name("products.store");
        Route::post("/products/update/{id}", "update")->name("products.update");
        Route::post("/products/update-stock/{id}", "updateStock")->name("updateStock");
        Route::post("/products/status/{id}", "updateStatus")->name("updateStatus");
    });

    Route::get('/products/stock-movements/{id}', [ProductController::class, 'getStockMovements'])->name('products.stock-movements');
    Route::get('/stock-movements', [ProductController::class, 'indexStockMovements'])
    ->name('stock-movements.index');

     
 
    Route::resource("/cost", CostController::class);

    // Diler All Route
    // routes/web.php
    Route::get('/dealers', [DealerController::class, 'index'])->name('dealers.index');
    Route::get('/dealers/list', [DealerController::class, 'list'])->name('dealers.list');
    Route::post('/dealers/store', [DealerController::class, 'store'])->name('dealers.store');
    Route::post('/dealers/{dealer}/update', [DealerController::class, 'update'])->name('dealers.update');
    Route::delete('/dealers/{dealer}', [DealerController::class, 'destroy'])->name('dealers.destroy');

    // Deller Sells
    Route::get('/dealer-sales', [SaleController::class, 'dealerSalePage'])->name('dealer.sales.page');
    Route::post('/dealer-sales/store', [SaleController::class, 'storeDealerSale'])->name('dealer.sales.store');


    Route::get('/sales-reportall', [SaleController::class, 'salesReportall'])->name('sales.reportall');
    
    // card
    // Route::get('/cards/create', [CardController::class, 'create'])->name('cards.create');
    Route::post('/cards/store', [CardController::class, 'createCard'])->name('cards.store');
    Route::post('/cards/print', [CardController::class, 'cardprint'])->name('cards.print'); 
    Route::get('/cards/check', [CardController::class, 'index'])->name('cards.index');
    Route::post('/cards/check', [CardController::class, 'check'])->name('cards.check');
    Route::post('/cards/process/{id}', [CardController::class, 'process'])->name('cards.process');
    Route::post('/cards/search-chef', [CardController::class, 'searchChef'])->name('cards.searchChef');

   

    Route::get('/claimed-cards', [CardController::class, 'claimedCards'])->name('claimed.cards');

    // Chef
    Route::get('/chef/create', [ChefController::class, 'create'])->name('chef.create');
    Route::post('/chef/store', [ChefController::class, 'store'])->name('chef.store');
    Route::put('/chef/{chef}', [ChefController::class, 'update'])->name('chef.update');
    Route::delete('/chef/{chef}', [ChefController::class, 'destroy'])->name('chef.destroy');
   
    


    // Acount Menagment 
    Route::get('/acount/create', [AcountController::class, 'create'])->name('acount.create');
    Route::post('/acount/store', [AcountController::class, 'store'])->name('acount.store');
    // Route::get('/acount/{user}/edit', [AcountController::class, 'edit'])->name('acount.edit');
    Route::put('/acount/{user}', [AcountController::class, 'update'])->name('acount.update');
    Route::delete('/acount/{user}', [AcountController::class, 'destroy'])->name('acount.destroy');
    // ‍Sr Pay
    Route::get('/payment-claim', [CardController::class, 'show'])->name('payment.claim.show');
    Route::post('/payment-claim/{user}', [CardController::class, 'confirm'])->name('payment.claim.confirm');

     
    // ***********--------------***********

    Route::get('/allcustomerdues', [SaleController::class,'allcustomerdues'])->name('allcustomerdues'); 
    // Route::get('/alldcustomerdues', [SaleController::class,'alldcustomerdues'])->name('alldcustomerdues'); 


    Route::get('/alldealerdues', [SaleController::class,'alldealerdues'])->name('alldealerdues');
    
    // কাস্টমারে কাছে বকেয়া।
    Route::get('/customers/{customer}/dues', [SaleController::class, 'customerDues'])->name('customers.dues'); 
    Route::get('/customerDuesserch/{customer}', [SaleController::class, 'customerDuesserch'])->name('customerDuesserch'); 
    Route::post('/customers/{customer}/pay-dues', [SaleController::class, 'payCustomerDues'])->name('customers.pay-dues');
    
    // ডিলারের কাছে বকেয়া।
    Route::get('/dealer/{customer}/dues', [SaleController::class, 'dealerDues'])->name('dealer.dues');
    Route::get('/dealerDuesserch/{customer}', [SaleController::class, 'dealerDuesserch'])->name('dealerDuesserch'); 
    Route::post('/dealer/{customer}/pay-dues', [SaleController::class, 'payDealerDues'])->name('dealer.pay-dues');

    Route::get('/paymentReport', [SaleController::class, 'paymentReport'])->name('payments.report');

    
    
    // এসআর তুলা বকেয়া
    // Page that shows select + (optionally) user payments
    Route::get('/srcustomers-dues', [PaymentController::class, 'srcustomersdues'])->name('srcustomersdues');

// একক payment confirm
    Route::post('/payments/{payment}/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');
    // সব payment confirm
    Route::post('/users/{user}/payments/confirm-all', [PaymentController::class, 'confirmAll'])->name('payments.confirmAll');

    Route::get('/srdealers-dues', [PaymentController::class, 'srdealersdues'])->name('srdealersdues');
    
    Route::get('/customers/{customer}/statement', [SaleController::class, 'customerStatement'])->name('customers.statement');
    Route::get('/diller/{customer}/statement', [SaleController::class, 'dillerStatement'])->name('diller.statement');

    // ১. দৈনিক বিক্রয় এন্ট্রি মডিউল
    Route::prefix('morich/daily-sale')->name('morich.daily-sale.')->group(function () {
        Route::get('/create', [MorichDailySaleController::class, 'create'])->name('create');
        Route::post('/store', [MorichDailySaleController::class, 'store'])->name('store');
        Route::get('/summary/{morichDailySale}', [MorichDailySaleController::class, 'summary'])->name('summary');
        Route::get('/reports', [MorichDailySaleController::class, 'reports'])->name('reports');
    }); 

    // ২. পণ্য ও স্টক ম্যানেজমেন্ট মডিউল
    Route::prefix('morich')->name('morich.')->group(function () {
        Route::resource('products', MorichProductController::class);
        Route::get('products/stock-report', [MorichProductController::class, 'show'])->name('products.stock-report');
        Route::get('sreport', [MorichProductController::class, 'salesReport'])->name('sreport');
        Route::get('/stock-entries', [MorichProductController::class, 'stockEntries'])->name('products.stock-entries');
        Route::get('/stock-movement', [MorichProductController::class, 'stockMovementReport'])->name('products.stock-movement'); 
        Route::get('/date-wise-ledger', [MorichProductController::class, 'dateWiseLedger'])->name('products.date-wise-ledger');
    });


 
// POS থেকে দ্রুত কাস্টমার যুক্ত করা
    
    Route::post('/dealers/quick-add', [DealerController::class, 'quickAdd'])->name('dealers.quick.add');

    Route::prefix('custom')->name('custom.')->group(function () {
   
    // কাস্টমার তালিকা, তৈরি এবং সংরক্ষণ
    Route::get('/customers', [CustomLedgerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomLedgerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomLedgerController::class, 'store'])->name('customers.store');





    // লেনদেন সংরক্ষণ
    Route::post('/transactions/{customer}', [CustomLedgerController::class, 'storeTransaction'])->name('transactions.store');

    // স্টেটমেন্ট পেজ
    Route::get('/customers/{customer}/statement', [CustomLedgerController::class, 'statement'])->name('customers.statement');
    });

    Route::get('/reports/product-sales-analysis', [ReportController::class, 'productSalesAnalysis'])->name('reports.product-sales-analysis');

    Route::get('/sales/{sale}/pos-print', [SaleController::class, 'printPos'])->name('sales.pos.print');
    

    // Route::get("/dipo-sales", [PosController::class, "diposale"])->name("dipo.sale");
    Route::get('/pos/customer', function () {
     
        return Customer::where('is_dipu','=',1)->get(['id','name','number','shop_name','shop_address']);
        //fgdfg

    })->name('dipu.customer');
    // ================================ Bluetooth Print ==========================
    Route::get('/sales/{sale}/bluetooth-print', [SaleController::class, 'printBluetooth'])
        ->name('sales.bluetooth.print');

    // নতুন POS Print route
Route::get('/customers/{customer}/statement-pos-print', [SaleController::class, 'customerStatementPosPrint'])
    ->name('customers.statement.pos.print');
Route::get('/diller/{customer}/statement-pos-print', [SaleController::class, 'dillerStatementPosPrint'])
    ->name('diller.statement.pos.print');

// Customer Bluetooth Statement
Route::get('/customers/{customer}/statement/bluetooth', [SaleController::class, 'customerStatementBluetooth'])
    ->name('customers.statement.bluetooth');

// Dealer Bluetooth Statement  
Route::get('/diller/{customer}/statement/bluetooth', [SaleController::class, 'dillerStatementBluetooth'])
    ->name('diller.statement.bluetooth'); 
    
});  








    

require __DIR__ . "/auth.php";
