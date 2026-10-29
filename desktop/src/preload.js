'use strict';

const { contextBridge, ipcRenderer } = require('electron');

// Hanya halaman lokal aplikasi (file://) yang boleh memakai API ini,
// bukan halaman web Savve yang dimuat dari server.
if (location.protocol === 'file:') {
  contextBridge.exposeInMainWorld('savveDesktop', {
    setup: (options) => ipcRenderer.invoke('savve:setup', options),
    info: () => ipcRenderer.invoke('savve:info'),
    retry: () => ipcRenderer.invoke('savve:retry'),
    changeMode: () => ipcRenderer.invoke('savve:change-mode'),
    printerSettings: () => ipcRenderer.invoke('savve:printer-settings'),
    savePrinterSettings: (data) => ipcRenderer.invoke('savve:printer-settings-save', data),
    testPrinter: (printer, label) => ipcRenderer.invoke('savve:printer-test', { printer, label }),
  });
} else {
  // Halaman Savve dari server: hanya fungsi cetak dengan ukuran kertas nota
  contextBridge.exposeInMainWorld('savveDesktop', {
    printNota: (size) => ipcRenderer.invoke('savve:print-nota', size),
  });
}
