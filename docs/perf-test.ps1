param(
    [string]$Url = "http://localhost:5022/api/pendientes?page=1&por_pagina=5",
    [int]$Requests = 100
)

$token = Read-Host "Pega el Bearer Token JWT"

$headers = @{
    Authorization = "Bearer $token"
}

Write-Host ""
Write-Host "Calentando endpoint..."

# 10 solicitudes de calentamiento
for ($i = 1; $i -le 10; $i++) {
    try {
        $response = Invoke-WebRequest `
            -Uri $Url `
            -Headers $headers `
            -Method GET `
            -UseBasicParsing

        if ($response.StatusCode -ne 200) {
            Write-Host "Error durante calentamiento: $($response.StatusCode)"
            exit
        }
    }
    catch {
        Write-Host "Error durante calentamiento:"
        Write-Host $_.Exception.Message
        exit
    }
}

Write-Host "Ejecutando $Requests solicitudes..."
Write-Host ""

$latencias = @()
$exitosas = 0
$fallidas = 0

$totalWatch = [System.Diagnostics.Stopwatch]::StartNew()

for ($i = 1; $i -le $Requests; $i++) {

    $watch = [System.Diagnostics.Stopwatch]::StartNew()

    try {
        $response = Invoke-WebRequest `
            -Uri $Url `
            -Headers $headers `
            -Method GET `
            -UseBasicParsing

        $watch.Stop()

        $latencias += $watch.Elapsed.TotalMilliseconds

        if ($response.StatusCode -eq 200) {
            $exitosas++
        }
        else {
            $fallidas++
        }
    }
    catch {
        $watch.Stop()

        $latencias += $watch.Elapsed.TotalMilliseconds
        $fallidas++
    }
}

$totalWatch.Stop()

$ordenadas = $latencias | Sort-Object

$p95Index = [Math]::Ceiling(
    0.95 * $ordenadas.Count
) - 1

$p95 = $ordenadas[$p95Index]

$promedio = (
    $latencias |
    Measure-Object -Average
).Average

$minimo = (
    $latencias |
    Measure-Object -Minimum
).Minimum

$maximo = (
    $latencias |
    Measure-Object -Maximum
).Maximum

$segundos = $totalWatch.Elapsed.TotalSeconds

$reqPorSegundo = $Requests / $segundos

Write-Host ""
Write-Host "========== RESULTADOS =========="
Write-Host "URL:               $Url"
Write-Host "Solicitudes:       $Requests"
Write-Host "Exitosas:          $exitosas"
Write-Host "Fallidas:          $fallidas"
Write-Host ("Tiempo total:      {0:N2} s" -f $segundos)
Write-Host ("Req/s:             {0:N2}" -f $reqPorSegundo)
Write-Host ("Latencia promedio: {0:N2} ms" -f $promedio)
Write-Host ("Latencia p95:      {0:N2} ms" -f $p95)
Write-Host ("Latencia minima:   {0:N2} ms" -f $minimo)
Write-Host ("Latencia maxima:   {0:N2} ms" -f $maximo)
Write-Host "================================"