; Izinkan HP kasir di WiFi lokal tersambung ke server Savve (port 8443 HTTPS, 8080 HTTP)
!macro customInstall
  nsExec::Exec 'netsh advfirewall firewall delete rule name="Savve Server"'
  nsExec::Exec 'netsh advfirewall firewall add rule name="Savve Server" dir=in action=allow protocol=TCP localport=8443,8080 profile=any'
!macroend

!macro customUnInstall
  nsExec::Exec 'netsh advfirewall firewall delete rule name="Savve Server"'
!macroend
