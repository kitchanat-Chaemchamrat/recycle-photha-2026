<?php
requireRole(['super_admin', 'admin']);

$houses = $pdo->query("
    SELECT h.id, h.house_no, h.owner_name, h.member_count, s.soi_name
    FROM houses h
    JOIN sois s ON h.soi_id = s.id
    ORDER BY s.soi_name, h.house_no, h.id
")->fetchAll();

$housesJson = json_encode($houses, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">สร้างบัตรประจำบ้าน</h1>
</div>

<div class="card">
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label fw-bold">ค้นหาบ้าน</label>
            <input type="text" class="form-control" id="houseSearch" placeholder="พิมพ์เลขที่บ้าน ชื่อเจ้าของ หรือชื่อซอย">
        </div>

        <div class="border rounded p-3 bg-white" style="max-height: 420px; overflow:auto;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="fw-bold">เลือกบ้านที่ต้องการพิมพ์</div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAllBtn">เลือกทั้งหมด</button>
            </div>

            <div class="row g-2" id="houseList">
                <?php foreach ($houses as $house): ?>
                    <?php $label = $house['soi_name'] . ' - ' . $house['house_no'] . ' (' . $house['owner_name'] . ')'; ?>
                    <div class="col-12 house-item" data-search="<?php echo htmlspecialchars(mb_strtolower($label)); ?>">
                        <label class="border rounded p-2 w-100 d-flex align-items-start gap-2 bg-light">
                            <input class="form-check-input mt-1 house-checkbox" type="checkbox" value="<?php echo $house['id']; ?>">
                            <span>
                                <span class="d-block fw-semibold"><?php echo htmlspecialchars($house['house_no']); ?></span>
                                <span class="d-block small text-muted"><?php echo htmlspecialchars($house['owner_name']); ?></span>
                                <span class="d-block small text-muted"><?php echo htmlspecialchars($house['soi_name']); ?> | สมาชิก <?php echo (int)$house['member_count']; ?> คน</span>
                            </span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="button" class="btn btn-primary-custom" id="generatePdfBtn">สร้าง PDF สำหรับพิมพ์</button>
            <a class="btn btn-outline-secondary" href="index.php?page=house">กลับไปจัดการบ้าน</a>
        </div>
        <div class="form-text mt-3">
            ระบบจะสร้างบัตรขนาดเท่าบัตรเครดิต พื้นหลังสีขาวลาย Guilloche สีน้ำเงิน และแนบ QR Code ไปยังหน้ารายละเอียดบ้าน
        </div>
    </div>
</div>

<div id="houseCardStage" style="position: fixed; left: -10000px; top: -10000px; width: 0; height: 0; overflow: hidden;"></div>

<style>
.house-card-sheet {
    width: 325px;
    height: 204px;
    position: relative;
    background-color: #fff;
    border: 1px solid #d5e3f1;
    border-radius: 16px;
    overflow: hidden;
    font-family: 'Sarabun', sans-serif;
}
.house-card-sheet .card-inner {
    position: absolute;
    inset: 9px;
    border: 1px solid rgba(31,95,168,0.35);
    border-radius: 12px;
    padding: 10px 12px;
}
.house-card-sheet .title {
    font-family: 'Prompt', sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: #fff;
    line-height: 1;
    background: #5aa8ff;
    padding: 6px 10px 5px;
    border-radius: 8px;
    display: inline-block;
}
.house-card-sheet .subtitle {
    display: none;
}
.house-card-sheet .meta {
    font-family: 'Prompt', sans-serif;
    font-size: 10px;
    line-height: 1.25;
    color: #111827;
}
.house-card-sheet .label {
    font-family: 'Sarabun', sans-serif;
    font-size: 8px;
    color: #4b5563;
}
.house-card-sheet .qr {
    position: absolute;
    right: 10px;
    top: 34px;
    width: 78px;
    height: 78px;
    background: #fff;
    padding: 4px;
    border: 1px solid #c9d9ea;
    border-radius: 8px;
}
.house-card-sheet .line {
    display: none;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const houses = <?php echo $housesJson; ?>;
    const search = document.getElementById('houseSearch');
    const items = document.querySelectorAll('.house-item');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const generateBtn = document.getElementById('generatePdfBtn');
    const stage = document.getElementById('houseCardStage');

    search.addEventListener('input', function() {
        const term = search.value.trim().toLowerCase();
        items.forEach(item => {
            item.style.display = (item.dataset.search || '').includes(term) ? '' : 'none';
        });
    });

    selectAllBtn.addEventListener('click', function() {
        const visible = Array.from(items).filter(item => item.style.display !== 'none');
        const anyUnchecked = visible.some(item => !item.querySelector('.house-checkbox').checked);
        visible.forEach(item => {
            item.querySelector('.house-checkbox').checked = anyUnchecked;
        });
    });

    generateBtn.addEventListener('click', async function() {
        const ids = Array.from(document.querySelectorAll('.house-checkbox:checked')).map(cb => String(cb.value));
        const selected = houses.filter(h => ids.includes(String(h.id)));

        if (!selected.length) {
            alert('กรุณาเลือกบ้านอย่างน้อย 1 หลัง');
            return;
        }

        generateBtn.disabled = true;
        generateBtn.textContent = 'กำลังสร้าง PDF...';
        stage.innerHTML = '';

        try {
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: [85.6, 53.98] });

            for (let i = 0; i < selected.length; i++) {
                const house = selected[i];
                const card = document.createElement('div');
                card.className = 'house-card-sheet';
                const detailUrl = `${window.location.origin}/house_detail.php?id=${encodeURIComponent(house.id)}`;
                const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(detailUrl)}`;
                card.innerHTML = `
                    <div class="card-inner">
                        <div class="title">บัตรประจำบ้าน</div>
                        <div class="subtitle">Community Waste Management</div>
                        <div class="line"></div>
                        <img class="qr" src="${qrUrl}" crossorigin="anonymous" />
                        <div style="margin-top: 24px; padding-right: 92px;">
                            <div class="label">เลขที่บ้าน</div>
                            <div class="meta">${house.house_no}</div>
                            <div class="label" style="margin-top: 6px;">เจ้าของบ้าน</div>
                            <div class="meta">${house.owner_name}</div>
                            <div class="label" style="margin-top: 6px;">ซอย</div>
                            <div class="meta">${house.soi_name}</div>
                            <div class="label" style="margin-top: 6px;">สมาชิก (คน)</div>
                            <div class="meta">${house.member_count}</div>
                        </div>
                    </div>
                `;
                stage.appendChild(card);

                const canvas = await html2canvas(card, {
                    scale: 3,
                    useCORS: true,
                    backgroundColor: '#ffffff'
                });
                const imgData = canvas.toDataURL('image/png');
                if (i > 0) {
                    pdf.addPage([85.6, 53.98], 'landscape');
                }
                pdf.addImage(imgData, 'PNG', 0, 0, 85.6, 53.98);
            }

            pdf.save(`house_cards_${new Date().toISOString().slice(0, 10)}.pdf`);
        } catch (err) {
            alert('สร้าง PDF ไม่สำเร็จ: ' + (err && err.message ? err.message : err));
        } finally {
            generateBtn.disabled = false;
            generateBtn.textContent = 'สร้าง PDF สำหรับพิมพ์';
        }
    });
});
</script>
