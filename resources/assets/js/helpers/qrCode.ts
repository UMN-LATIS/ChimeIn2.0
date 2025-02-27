import QRCode from "qrcode";

async function createSvgQRCode(content: string) {
  const svgString = await QRCode.toString(content, {
    errorCorrectionLevel: "H",
    margin: 2,
    type: "svg",
  });

  const blob = new Blob([svgString], { type: "image/svg+xml" });
  return URL.createObjectURL(blob);
}

async function createPngQRCode(text: string) {
  return QRCode.toDataURL(text, {
    type: "image/png",
    errorCorrectionLevel: "H",
    margin: 2,
  });
}

/**
 * Generate QR code. This can be
 * used for img src
 */
export function generateQRCode(content: string, type: "svg" | "png" = "svg") {
  if (!content || typeof content !== "string") {
    throw new Error("generateQRCode only accept string as the first argument");
  }

  return type === "svg" ? createSvgQRCode(content) : createPngQRCode(content);
}

/**
 * Download QR code
 */
export async function downloadQRCode(
  content,
  {
    type,
    filename,
  }: {
    type: "svg" | "png";
    filename: string;
  }
) {
  const qrCodeSrc = await generateQRCode(content, type);

  const a = document.createElement("a");
  a.href = qrCodeSrc;
  a.download = filename;
  a.click();

  // cleanup
  if (qrCodeSrc.startsWith("blob:")) {
    URL.revokeObjectURL(qrCodeSrc);
  }
}
