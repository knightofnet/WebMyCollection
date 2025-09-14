$script:ErrorActionPreference = "Stop"

# Setup session options
$script:sessionOptions = $null;

$pathProjet = "."

function clearRemoteFile()
{


  $session = New-Object WinSCP.Session

  try
  {
    # Connect
    $session.Open($sessionOptions)

    $filesToDelete = $session.EnumerateRemoteFiles("/home/u278089789/public_html/myCollection", "*.js", [WinSCP.EnumerationOptions]::None);
    $filesToDelete += $session.EnumerateRemoteFiles("/home/u278089789/public_html/myCollection", "style*.css", [WinSCP.EnumerationOptions]::None);
    $filesToDelete += $session.EnumerateRemoteFiles("/home/u278089789/public_html/myCollection", "index.html", [WinSCP.EnumerationOptions]::None);
    $filesToDelete += $session.EnumerateRemoteFiles("/home/u278089789/public_html/myCollection", "favicon.ico", [WinSCP.EnumerationOptions]::None);

    foreach ($file in $filesToDelete)
    {
      Write-Host "Deleting file: $( $file.FullName )"
      $session.RemoveFile($file.FullName) | Out-Null
    }

    $folderImg = "/home/u278089789/public_html/myCollection/img"
    $isFolderExits = $session.FileExists($folderImg);
    if ($isFolderExits) {
      Write-Host "Supression dossier $folderImg";
      $session.RemoveFiles($folderImg);
    }

  }
  finally
  {
    # Disconnect, clean up
    $session.Dispose()
  }



}

$script:localDeployPath = "E:\Other\php\myCollection\myCollectionFront\dist\my-collection-front\browser"
$script:remotePath = "/home/u278089789/public_html/myCollection/";

function uploadElements($elements) {
  $session = New-Object WinSCP.Session

  try
  {
    # Connect
    $session.Open($sessionOptions)

    foreach($element in $elements) {
      $fullpath = Join-Path $script:localDeployPath $element;
      if (Test-Path -Path $fullpath ) {
        Write-Host "Envoie de $element ...";
        $session.PutFiles($fullpath, $script:remotePath )
        Write-Host "Envoie de $element : OK";

      } else {
        Write-Host "$element n'existe pas";
      }
    }


  }
  finally
  {
    # Disconnect, clean up
    $session.Dispose()
  }

}

function main()
{
  [Reflection.Assembly]::LoadFile("E:\Logiciels\WinScpAssembly\WinSCPnet.dll") | Out-Null;

  $script:sessionOptions = New-Object WinSCP.SessionOptions -Property @{
    Protocol = [WinSCP.Protocol]::Sftp
    HostName = "109.106.246.182"
    PortNumber = 65002
    UserName = "u278089789"
    Password = "6HSdfeTTay6KIDsp5F8G"
    SshHostKeyFingerprint = "ssh-ed25519 255 4nF4+DQcS0jzAbptLeQh9LATY8KmSMNxhl9BPTeGRUY"


  }

  # Build angular
  ng build --configuration production --base-href /myCollection/


  # Clear remote files
  clearRemoteFile;

  # Upload files
  $elements = @(
    "/img",
    "favicon.ico",
    "index.html",
    "main-*.js",
    "polyfills-*.js",
    "styles-*.css"
  );

  uploadElements -elements $elements;



}

main


