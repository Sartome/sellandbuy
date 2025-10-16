<?php $pageTitle = $pageTitle ?? 'Analyses'; ?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <h1>Analyses</h1>
    <div class="grid two">
        <div class="card">
            <div class="body">
                <h3>Produits vendus (30 jours)</h3>
                <canvas id="salesCount"></canvas>
            </div>
        </div>
        <div class="card">
            <div class="body">
                <h3>Revenus (30 jours)</h3>
                <canvas id="salesAmount"></canvas>
            </div>
        </div>
        <div class="card" style="grid-column: span 2;">
            <div class="body">
                <h3>Comptes créés (30 jours)</h3>
                <canvas id="usersCount"></canvas>
            </div>
        </div>
    </div>
</main>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const salesCountData = <?php echo json_encode($salesCount ?? []); ?>;
const salesAmountData = <?php echo json_encode($salesAmount ?? []); ?>;
const usersCountData = <?php echo json_encode($usersCount ?? []); ?>;

function toChartData(rows, valueKey) {
  return {
    labels: rows.map(r => r.d),
    datasets: [{
      label: valueKey,
      data: rows.map(r => Number(r[valueKey] || 0)),
      borderColor: '#7c3aed',
      backgroundColor: 'rgba(124,58,237,0.2)'
    }]
  };
}

new Chart(document.getElementById('salesCount'), { type: 'line', data: toChartData(salesCountData, 'c') });
new Chart(document.getElementById('salesAmount'), { type: 'line', data: toChartData(salesAmountData, 's') });
new Chart(document.getElementById('usersCount'), { type: 'line', data: toChartData(usersCountData, 'c') });
</script>


