// QR Code Generation
function initQRCodes() {
    document.querySelectorAll('[data-qr]').forEach(element => {
        const qrContainer = element.querySelector('.qr-code-container');
        if (!qrContainer) return;
        
        const qrElement = qrContainer.querySelector('div');
        const qrUrl = element.dataset.qr;
        
        // Create QR code
        new QRCode(qrElement, {
            text: qrUrl,
            width: 100,
            height: 100,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
        
        // Add download functionality
        const downloadBtn = qrContainer.querySelector('.download-qr');
        if (downloadBtn) {
            downloadBtn.addEventListener('click', () => {
                const canvas = qrElement.querySelector('canvas');
                const image = canvas.toDataURL("image/png");
                const link = document.createElement('a');
                link.href = image;
                link.download = 'pet-qr-code.png';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        }
    });
} 