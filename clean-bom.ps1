$files = @(
    "app\Http\Requests\Auth\LoginRequest.php",
    "app\Http\Requests\Auth\RegisterRequest.php",
    "app\Http\Requests\CheckoutRequest.php",
    "app\Http\Requests\AddToCartRequest.php",
    "app\Http\Requests\UpdateCartItemRequest.php",
    "app\Http\Requests\Admin\UpdateProductRequest.php",
    "app\Http\Requests\Admin\DropRequest.php",
    "app\Http\Requests\Admin\StoreProductRequest.php",
    "app\Http\Requests\Admin\UpdateOrderStatusRequest.php",
    "resources\views\layouts\partials\header.blade.php",
    "resources\views\auth\passwords\email.blade.php",
    "resources\views\auth\passwords\reset.blade.php"
)

foreach ($f in $files) {
    if (Test-Path $f) {
        $content = Get-Content -Path $f -Raw -Encoding UTF8
        [System.IO.File]::WriteAllText($f, $content, (New-Object System.Text.UTF8Encoding($false)))
        Write-Host "Nettoyé : $f"
    } else {
        Write-Host "Introuvable : $f"
    }
}
