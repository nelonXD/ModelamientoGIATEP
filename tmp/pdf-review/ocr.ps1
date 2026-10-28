Add-Type -AssemblyName System.Runtime.WindowsRuntime
[Windows.Storage.StorageFile,Windows.Storage,ContentType=WindowsRuntime] | Out-Null
[Windows.Graphics.Imaging.BitmapDecoder,Windows.Foundation,ContentType=WindowsRuntime] | Out-Null
[Windows.Media.Ocr.OcrEngine,Windows.Foundation,ContentType=WindowsRuntime] | Out-Null
$asTask = ([System.WindowsRuntimeSystemExtensions].GetMethods() | Where-Object { $_.Name -eq 'AsTask' -and $_.IsGenericMethod -and $_.GetParameters().Count -eq 1 -and $_.GetParameters()[0].ParameterType.Name -eq 'IAsyncOperation`1' })[0]
function Await($op,$type) { $task=$asTask.MakeGenericMethod($type).Invoke($null,@($op)); $task.Wait(); $task.Result }
$engine=[Windows.Media.Ocr.OcrEngine]::TryCreateFromUserProfileLanguages()
Get-ChildItem -LiteralPath 'C:/laragon/www/Pruba-boceto/tmp/pdf-review' -Filter 'res-*.png' | ForEach-Object {
 $file=Await ([Windows.Storage.StorageFile]::GetFileFromPathAsync($_.FullName.Replace('/','\'))) ([Windows.Storage.StorageFile])
 $stream=Await ($file.OpenAsync([Windows.Storage.FileAccessMode]::Read)) ([Windows.Storage.Streams.IRandomAccessStream])
 $decoder=Await ([Windows.Graphics.Imaging.BitmapDecoder]::CreateAsync($stream)) ([Windows.Graphics.Imaging.BitmapDecoder])
 $bitmap=Await ($decoder.GetSoftwareBitmapAsync()) ([Windows.Graphics.Imaging.SoftwareBitmap])
 $result=Await ($engine.RecognizeAsync($bitmap)) ([Windows.Media.Ocr.OcrResult])
 "PAGE $($_.BaseName)"; $result.Text
 $stream.Dispose()
}

