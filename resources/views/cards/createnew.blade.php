          
@extends('adminlte::page')

@section('title', 'কার্ড যাচাইকরণ')

@section('content_header')
    <h1>কার্ড যাচাইকরণ</h1>
@stop

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-body text-center">
            <h3 class="mb-3">কোড চেক করুন</h3>
            <p class="text-muted">ম্যানুয়ালি কোড লিখুন অথবা ক্যামেরা দিয়ে ছবি তুলে স্বয়ংক্রিয়ভাবে কোডটি নিয়ে আসুন।</p>
            
            <div class="input-group mb-3 mx-auto" style="max-width: 450px;">
                <input type="text" id="code" placeholder="কার্ডের কোড লিখুন..." class="form-control form-control-lg text-center">
                <div class="input-group-append">
                    <button id="checkBtn" class="btn btn-primary btn-lg"><i class="fas fa-check-circle mr-1"></i> চেক করুন</button>
                </div>
            </div>

            <div class="ocr-buttons mt-4">
                <button id="captureBtn" class="btn btn-success"><i class="fas fa-camera mr-1"></i> ছবি তুলুন</button>
                <label for="uploadInput" class="btn btn-info mb-0"><i class="fas fa-image mr-1"></i> ছবি আপলোড করুন</label>
                <input type="file" id="uploadInput" accept="image/*" style="display: none;">
            </div>

            <div id="ocrStatus" class="mt-3" style="display: none;">
                <div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>
                <p class="text-primary mt-2">ছবি থেকে কোড সনাক্ত করা হচ্ছে...</p>
            </div>
            
            {{-- ইমেজ প্রি-প্রসেসিং এর জন্য একটি অদৃশ্য ক্যানভাস --}}
            <canvas id="imageCanvas" style="display: none;"></canvas>
        </div>
    </div> 
</div>
@stop
 
@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    .ocr-buttons .btn { padding: 10px 20px; font-size: 1rem; margin: 5px; }
</style>
@stop
 
@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src='https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js'></script>

<script>
(async function () {
    const checkBtn = document.getElementById("checkBtn");
    const codeInput = document.getElementById("code");
    const captureBtn = document.getElementById("captureBtn");
    const uploadInput = document.getElementById("uploadInput");
    const ocrStatus = document.getElementById("ocrStatus");
    const canvas = document.getElementById('imageCanvas');
    const ctx = canvas.getContext('2d', { willReadFrequently: true });

    // শুধুমাত্র সাদা পিক্সেল রাখার জন্য ফাংশন
    function preprocessImageForOCR(imageFile) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = new Image();
                img.onload = function() {
                    canvas.width = img.width;
                    canvas.height = img.height;
                    ctx.drawImage(img, 0, 0);
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const data = imageData.data;
                    for (let i = 0; i < data.length; i += 4) {
                        const r = data[i], g = data[i + 1], b = data[i + 2];
                        if (r < 200 || g < 200 || b < 200) {
                            data[i] = 0; data[i + 1] = 0; data[i + 2] = 0;
                        }
                    }
                    ctx.putImageData(imageData, 0, 0);
                    resolve(canvas.toDataURL('image/png'));
                };
                img.onerror = reject;
                img.src = event.target.result;
            };
            reader.onerror = reject;
            reader.readAsDataURL(imageFile);
        });
    }

    // OCR (ছবি থেকে টেক্সট) ফাংশন
    async function processImage(imageFile) {
        ocrStatus.style.display = 'block';
        checkBtn.disabled = true;
        captureBtn.disabled = true;

        try {
            const processedImage = await preprocessImageForOCR(imageFile);
            const worker = await Tesseract.createWorker('eng');
            
            // Tesseract কে বলা হচ্ছে যে 'e' এবং 'E' অক্ষর দুটিকে উপেক্ষা করতে
            await worker.setParameters({
                tessedit_char_blacklist: 'eE',
            });

            const { data: { text } } = await worker.recognize(processedImage);
            await worker.terminate();

            const cleanedText = text.replace(/[^a-zA-Z0-9]/g, '');
            const potentialCodes = cleanedText.match(/[a-zA-Z0-9]{10}/g) || [];
            const foundCode = potentialCodes.length > 0 ? potentialCodes[0] : null;

            if (foundCode) {
                codeInput.value = foundCode;
                Swal.fire({
                    icon: 'success',
                    title: 'কোড সনাক্ত হয়েছে!',
                    text: `সনাক্ত করা কোড: ${foundCode}`,
                    timer: 2500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('ব্যর্থ!', 'ছবি থেকে ১০ অক্ষরের কোনো কোড সনাক্ত করা যায়নি। অনুগ্রহ করে আরও স্পষ্ট ছবি দিন।', 'error');
            }

        } catch (error) {
            console.error(error);
            Swal.fire('ত্রুটি!', 'OCR প্রসেসিং এ সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।', 'error');
        } finally {
            ocrStatus.style.display = 'none';
            checkBtn.disabled = false;
            captureBtn.disabled = false;
            uploadInput.value = ''; 
        }
    }

    // ইভেন্ট হ্যান্ডলার
    uploadInput.addEventListener('change', (event) => {
        if (event.target.files && event.target.files[0]) {
            processImage(event.target.files[0]);
        }
    });

    captureBtn.addEventListener('click', () => {
        const captureInput = document.createElement('input');
        captureInput.type = 'file';
        captureInput.accept = 'image/*';
        captureInput.capture = 'environment';
        captureInput.addEventListener('change', (event) => {
            if (event.target.files && event.target.files[0]) {
                processImage(event.target.files[0]);
            }
        });
        captureInput.click();
    });

    // আপনার বিদ্যমান কার্ড চেক করার লজিক (অপরিবর্তিত)
    const checkUrl = "{{ route('cards.check') }}";
    const searchChefUrl = "{{ route('cards.searchChef') }}";
    const processBaseUrl = "{{ url('cards/process') }}";

    checkBtn.addEventListener("click", async function () {
        let code = (codeInput.value || "").trim();
        if (!code) {
            return Swal.fire("সতর্কতা", "⚠️ অনুগ্রহ করে কোড লিখুন অথবা ছবি তুলুন।", "warning");
        }
        checkBtn.disabled = true;
        try {
            const res = await fetch(checkUrl, {
                method: "POST",
                headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}", "Content-Type": "application/json", "Accept": "application/json" },
                body: JSON.stringify({ code })
            });
            const resData = await res.json();
            if (resData.status === "error") {
                Swal.fire("ত্রুটি!", resData.message, "error");
                return;
            }
            if (resData.status === "valid") {
                const cardId = resData.card_id;
                const productName = resData.product_name || "";
                const confirmResult = await Swal.fire({
                    title: "✅ কোডটি সঠিক!",
                    html: `পণ্যের নাম: <b>${productName}</b><br><br>আপনি কি এই কার্ডটি কোনো বাবুর্চির নামে ইস্যু করতে চান?`,
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "হ্যাঁ, বাবুর্চি খুঁজুন",
                    cancelButtonText: "না, ব্যক্তিগতভাবে ব্যবহার করুন",
                    allowOutsideClick: false
                });
                if (confirmResult.isConfirmed) {
                    await handleChefAssignment(cardId);
                } else if (confirmResult.dismiss === Swal.DismissReason.cancel) {
                    await processCard(cardId, "no", null);
                }
            }
        } catch (err) {
            console.error(err);
            Swal.fire("ত্রুটি!", "নেটওয়ার্ক বা সার্ভারে সমস্যা হয়েছে। আবার চেষ্টা করুন।", "error");
        } finally {
            checkBtn.disabled = false;
        }
    });

    async function handleChefAssignment(cardId) {
        const { value: c_number } = await Swal.fire({
            title: "বাবুর্চির বহি নং দিন",
            input: "text",
            inputPlaceholder: "বহি নং...",
            showCancelButton: true,
            confirmButtonText: "খুঁজুন",
            inputValidator: (value) => !value && "অনুগ্রহ করে বহি নং প্রদান করুন"
        });
        if (c_number) {
            const chefRes = await fetch(searchChefUrl, {
                method: "POST",
                headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}", "Content-Type": "application/json", "Accept": "application/json" },
                body: JSON.stringify({ c_number: c_number.trim() })
            });
            const chefData = await chefRes.json();
            if (chefData.status === "error") {
                Swal.fire("ত্রুটি!", chefData.message, "error");
                return;
            }
            const chef = chefData.chef;
            const { isConfirmed } = await Swal.fire({
                title: "বাবুর্চি পাওয়া গেছে ✅",
                html: `নাম: <b>${chef.name}</b><br>মোবাইল: <b>${chef.mobile}</b><br><br>আপনি কি এই বাবুর্চির নামে কার্ডটি যুক্ত করতে চান?`,
                showCancelButton: true,
                confirmButtonText: "হ্যাঁ, যুক্ত করুন",
                cancelButtonText: "না, বাতিল করুন"
            });
            if (isConfirmed) {
                await processCard(cardId, "yes", c_number.trim());
            }
        }
    }

    async function processCard(cardId, assign, c_number) { 
        try {
            const res = await fetch(`${processBaseUrl}/${cardId}`, {
                method: "POST",
                headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}", "Content-Type": "application/json", "Accept": "application/json" },
                body: JSON.stringify({ assign, c_number })
            });
            const result = await res.json();
            if (result.status === "success") {
                Swal.fire("সফল!", result.message, "success");
                codeInput.value = "";
            } else {
                Swal.fire("ত্রুটি!", result.message || "কোথাও সমস্যা হয়েছে", "error");
            }
        } catch (err) {
            console.error(err);
            Swal.fire("ত্রুটি!", "কার্ড প্রসেস করার সময় নেটওয়ার্কে সমস্যা হয়েছে।", "error");
        }
    }
})();
$(document).ready(function() {
    const codeInput = $('#code');
    const checkBtn = $('#checkBtn');

    // ইনপুট ফিল্ডে ফোকাস করা
    codeInput.focus();

    // বারকোড স্ক্যানার সাধারণত ইনপুট শেষে "Enter" কী পাঠায়
    // আমরা সেই "Enter" কী প্রেস ইভেন্টটি ধরব
    codeInput.on('keypress', function(e) {
        // যদি Enter কী (keycode 13) চাপা হয়
        if (e.which === 13) {
            // ব্রাউজারের ডিফল্ট فرم সাবমিট বন্ধ করা
            e.preventDefault(); 
            
            // "চেক করুন" বাটনে ক্লিক করার মতো কাজ করানো
            checkBtn.click();
        }
    });

    checkBtn.on('click', function() {
        const code = codeInput.val().trim();
        if (!code) {
            alert('অনুগ্রহ করে একটি কোড লিখুন বা স্ক্যান করুন।');
            return;
        }

        // এখানে আপনার কোড চেক করার জন্য AJAX কল বা অন্য কোনো লজিক থাকবে
        console.log("Checking code:", code);
        // مثال:
        // $.ajax({
        //     url: '/cards/check-code',
        //     type: 'POST',
        //     data: { code: code },
        //     success: function(response) { ... },
        //     error: function(xhr) { ... }
        // });
    });

    // আপনার OCR বাটনগুলোর কোড এখানে অপরিবর্তিত থাকবে...
});
</script>
@stop 