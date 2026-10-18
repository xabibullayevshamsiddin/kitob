import sys
import json
import os
import argparse

try:
    import pymupdf
    from rapidocr_onnxruntime import RapidOCR
except ImportError:
    try:
        import fitz as pymupdf
        from rapidocr_onnxruntime import RapidOCR
    except ImportError as e:
        sys.stderr.write(f"Missing dependency: {e}\n")
        sys.exit(2)

def main():
    parser = argparse.ArgumentParser(description="OCR a PDF file into JSON pages")
    parser.add_argument("pdf_path", help="Path to PDF file")
    parser.add_argument("--max-pages", type=int, default=50, help="Maximum pages to OCR (default: 50)")
    parser.add_argument("--start-page", type=int, default=1, help="1-based start page (default: 1)")
    args = parser.parse_args()

    pdf_path = args.pdf_path
    if not os.path.isfile(pdf_path):
        sys.stderr.write(f"File not found: {pdf_path}\n")
        sys.exit(1)

    try:
        doc = pymupdf.open(pdf_path)
    except Exception as e:
        sys.stderr.write(f"Failed to open PDF: {e}\n")
        sys.exit(1)

    total_pdf_pages = len(doc)
    start_idx = max(0, args.start_page - 1)
    end_idx = min(total_pdf_pages, start_idx + args.max_pages)

    ocr = RapidOCR()
    extracted_pages = []

    for i in range(start_idx, end_idx):
        page_num = i + 1
        page = doc[i]
        try:
            pix = page.get_pixmap(dpi=150)
            img_bytes = pix.tobytes("png")
            result, _ = ocr(img_bytes)

            lines = []
            if result:
                for item in result:
                    line_text = item[1].strip()
                    # Filter out purely promotional eduportal watermark
                    if line_text == "http://eduportal.uz" or line_text == "eduportal.uz":
                        continue
                    if line_text:
                        lines.append(line_text)

            page_text = "\n".join(lines).strip()
            extracted_pages.append({
                "page_number": page_num,
                "text": page_text,
                "chars": len(page_text)
            })
        except Exception as e:
            extracted_pages.append({
                "page_number": page_num,
                "text": "",
                "chars": 0,
                "error": str(e)
            })

    output = {
        "success": True,
        "total_pdf_pages": total_pdf_pages,
        "processed_pages_count": len(extracted_pages),
        "pages": extracted_pages
    }

    # Output JSON in UTF-8
    sys.stdout.reconfigure(encoding='utf-8')
    print(json.dumps(output, ensure_ascii=False))

if __name__ == "__main__":
    main()
