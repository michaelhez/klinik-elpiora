<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title>Papan Antrian - <?= e(config('app.clinic.name', config('app.name'))) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: #0F172A;
            color: #F8FAFC;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .board-header {
            text-align: center;
            padding: 1.5rem 1rem 0.5rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            font-size: 1.1rem;
            color: #93C5FD;
            text-transform: uppercase;
        }
        .board-grid {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem 2rem 2.5rem;
        }
        .poly-card {
            background: #1E293B;
            border-radius: 16px;
            padding: 1.75rem;
            text-align: center;
            border: 1px solid #334155;
        }
        .poly-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: #94A3B8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
        }
        .now-serving-label {
            font-size: 0.75rem;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .now-serving-number {
            font-size: 4rem;
            font-weight: 800;
            color: #2563EB;
            line-height: 1.1;
            margin: 0.25rem 0 0.75rem;
            min-height: 4.4rem;
        }
        .next-label {
            font-size: 0.75rem;
            color: #64748B;
            text-transform: uppercase;
        }
        .next-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #CBD5E1;
        }
        .board-clock {
            text-align: center;
            padding-bottom: 1.5rem;
            color: #64748B;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="board-header"><i class="bi bi-heart-pulse-fill me-2"></i><?= e(config('app.clinic.name', config('app.name'))) ?> &mdash; Papan Antrian</div>
    <div class="board-grid" id="boardGrid">
        <?php foreach ($polyclinics as $poly): ?>
        <div class="poly-card" data-polyclinic-id="<?= e($poly['id']) ?>">
            <div class="poly-name"><?= e($poly['name']) ?></div>
            <div class="now-serving-label">Sedang Dipanggil</div>
            <div class="now-serving-number" data-field="current">-</div>
            <div class="next-label">Antrian Berikutnya</div>
            <div class="next-number" data-field="next">-</div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="board-clock" id="boardClock"></div>

    <script>
    function updateClock() {
        document.getElementById('boardClock').textContent = new Date().toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'medium' });
    }
    setInterval(updateClock, 1000);
    updateClock();

    async function refreshBoard() {
        try {
            const res = await fetch('/api/queue/board-data', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const payload = await res.json();
            if (!payload.success) return;

            payload.data.forEach((item) => {
                const card = document.querySelector(`[data-polyclinic-id="${item.polyclinic_id}"]`);
                if (!card) return;
                card.querySelector('[data-field="current"]').textContent = item.current || '-';
                card.querySelector('[data-field="next"]').textContent = item.next || '-';
            });
        } catch (e) {
            // silent fail, retry on next interval
        }
    }

    refreshBoard();
    setInterval(refreshBoard, 4000);
    </script>
</body>
</html>
