# Script untuk migrate fresh dan seed database
Write-Host "🔄 Menjalankan migrate:fresh..." -ForegroundColor Yellow
php artisan migrate:fresh --force

Write-Host "🌱 Menjalankan database seeder..." -ForegroundColor Yellow
php artisan db:seed

Write-Host "✅ Database berhasil di-reset dan di-seed!" -ForegroundColor Green
Write-Host "📊 Struktur database sudah menggunakan auto-increment ID" -ForegroundColor Green
Write-Host "🔗 Relationships sudah optimal untuk performance" -ForegroundColor Green

Write-Host "`n📋 Ringkasan perubahan:" -ForegroundColor Cyan
Write-Host "- ✅ Tabel provinsi: id (auto-increment) + kode (unique)" -ForegroundColor White
Write-Host "- ✅ Tabel kabupaten_kota: id (auto-increment) + provinsi_id (FK)" -ForegroundColor White  
Write-Host "- ✅ Tabel kecamatan: id (auto-increment) + kabupaten_kota_id (FK)" -ForegroundColor White
Write-Host "- ✅ Tabel crime_data: menggunakan ID untuk semua FK relationships" -ForegroundColor White
Write-Host "- ✅ API endpoints sudah diupdate untuk menggunakan ID" -ForegroundColor White
Write-Host "- ✅ Vue components sudah diupdate untuk struktur baru" -ForegroundColor White

Write-Host "`n🚀 Sistem siap digunakan dengan struktur database yang optimal!" -ForegroundColor Green
