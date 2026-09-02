<?php
requireRole(['super_admin', 'admin', 'collector']);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">สแกน QR ด้วยกล้อง</h1>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <div id="qr-reader" style="width: 100%; max-width: 520px; margin: 0 auto;"></div>
                <div class="text-center mt-3">
                    <button class="btn btn-outline-secondary me-2" id="startScanBtn">เริ่มสแกน</button>
                    <button class="btn btn-outline-danger" id="stopScanBtn" disabled>หยุดสแกน</button>
                </div>
                <div id="scanStatus" class="alert alert-info mt-3 mb-0">
                    พร้อมใช้งาน กล้องจะสแกน QR แล้วพาไปเปิดรายละเอียดบ้านหรือบันทึกขยะต่อได้ทันที
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">ผลการสแกน</h5>
                <div id="scanResult" class="text-muted">
                    ยังไม่ได้สแกน
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
const qrReader = new Html5Qrcode('qr-reader');
let scanning = false;

function setStatus(message, type = 'info') {
    const el = document.getElementById('scanStatus');
    el.className = 'alert alert-' + type + ' mt-3 mb-0';
    el.textContent = message;
}

function extractHouseId(text) {
    try {
        const url = new URL(text, window.location.href);
        const id = url.searchParams.get('id');
        return id ? parseInt(id, 10) : null;
    } catch (e) {
        const match = String(text).match(/id=(\d+)/i);
        return match ? parseInt(match[1], 10) : null;
    }
}

async function showResult(text) {
    const houseId = extractHouseId(text);
    const resultEl = document.getElementById('scanResult');

    if (!houseId) {
        resultEl.innerHTML = '<div class="alert alert-warning mb-0">QR นี้ไม่ใช่ลิงก์บ้านที่ระบบเข้าใจ</div>';
        setStatus('สแกนแล้ว แต่ไม่พบรหัสบ้านใน QR', 'warning');
        return;
    }

    const detailUrl = 'house_detail.php?id=' + encodeURIComponent(houseId);
    const recycleUrl = 'index.php?page=recycle&house_id=' + encodeURIComponent(houseId);

    resultEl.innerHTML = `
        <div class="alert alert-success">
            พบข้อมูลบ้านแล้ว รหัสบ้าน: <strong>${houseId}</strong>
        </div>
        <div class="d-grid gap-2">
            <a class="btn btn-primary-custom" href="${detailUrl}">เปิดรายละเอียดบ้าน</a>
            <a class="btn btn-outline-primary" href="${recycleUrl}">บันทึกน้ำหนักขยะต่อทันที</a>
        </div>
    `;
    setStatus('สแกนสำเร็จแล้ว เลือกการทำงานต่อได้ทันที', 'success');
}

async function startScan() {
    if (scanning) return;
    try {
        await qrReader.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            async (decodedText) => {
                await qrReader.stop();
                scanning = false;
                document.getElementById('startScanBtn').disabled = false;
                document.getElementById('stopScanBtn').disabled = true;
                setStatus('สแกน QR สำเร็จ', 'success');
                await showResult(decodedText);
            },
            () => {}
        );
        scanning = true;
        document.getElementById('startScanBtn').disabled = true;
        document.getElementById('stopScanBtn').disabled = false;
        setStatus('กำลังเปิดกล้องเพื่อสแกน QR...', 'info');
    } catch (error) {
        setStatus('เปิดกล้องไม่ได้ กรุณาตรวจสิทธิ์การใช้งานกล้องหรือใช้ https/localhost', 'danger');
    }
}

async function stopScan() {
    if (!scanning) return;
    try {
        await qrReader.stop();
    } catch (e) {}
    scanning = false;
    document.getElementById('startScanBtn').disabled = false;
    document.getElementById('stopScanBtn').disabled = true;
    setStatus('หยุดสแกนแล้ว', 'secondary');
}

document.getElementById('startScanBtn').addEventListener('click', startScan);
document.getElementById('stopScanBtn').addEventListener('click', stopScan);
</script>
