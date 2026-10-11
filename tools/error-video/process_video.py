"""
Error Background Video Processor: Multi-Error Stone Text Engraving
------------------------------------------------------------------
Replaces the baked-in foreign text on the tombstone in the error page background video
with unique, authentic Uzbek literary quotes carved into the stone for each HTTP error code:
(404, 403, 419, 429, 500, 503).

Usage:
    uv run --python 3.12 --with opencv-python --with pillow --with numpy python tools/error-video/process_video.py
"""

import os
import sys
import shutil
import subprocess
from pathlib import Path
import numpy as np
import cv2
from PIL import Image, ImageDraw, ImageFont

# ==============================================================================
# CONFIGURATION: Ibratli so'zlar (Unique quote for each error code)
# Edit lines for any error code below to customize the stone engraving.
# ==============================================================================
ERROR_QUOTES = {
    "404": [
        "ADASHMOQ HAM",
        "YO‘LNING BOSHI",
        "QIDIRGAN TOPAR"
    ],
    "403": [
        "SABR QIL, HAR ESHIK",
        "O‘Z VAQTIDA",
        "OCHILAJAKDIR"
    ],
    "419": [
        "G‘ANIMATDUR BU DAM",
        "FURSATNI BOYLAMA",
        "MUTOLAAGA QAYT"
    ],
    "429": [
        "SHOSHGANNING SHOSHI",
        "DONONING BARDOSHI",
        "SOKIN VARAQLA"
    ],
    "500": [
        "HAR BIR TO‘SIQDA",
        "BIR HIKMAT BOR",
        "SABR ET, INSON"
    ],
    "503": [
        "TIN OLGAN AQL",
        "KUCHGA TO‘LIB",
        "QAYTA BOSHLAYDI"
    ]
}

# Source video URL (original prompt media)
SOURCE_URL = "https://motionsites.org/assets/prompt-media/6fb049e38e9593f9699c.mp4"

# Path definitions
SCRIPT_DIR = Path(__file__).resolve().parent
PROJECT_ROOT = SCRIPT_DIR.parent.parent
SOURCE_VIDEO = SCRIPT_DIR / "source.mp4"
PUBLIC_MEDIA_DIR = PROJECT_ROOT / "public" / "media"
PUBLIC_ERRORS_DIR = PUBLIC_MEDIA_DIR / "errors"

# Stone region of interest in 1920x1080 frame
CROP_Y1, CROP_Y2 = 680, 920
CROP_X1, CROP_X2 = 120, 320


def find_ffmpeg() -> str:
    """Locate ffmpeg executable."""
    ffmpeg_bin = shutil.which("ffmpeg")
    if ffmpeg_bin:
        return ffmpeg_bin
    local_bin = Path("C:/Users/ASUS/.local/bin/ffmpeg.exe")
    if local_bin.exists():
        return str(local_bin)
    raise RuntimeError("ffmpeg executable not found in PATH or standard user directories.")


def get_font_path() -> str:
    """Find appropriate serif font with unicode support for Uzbek apostrophes."""
    candidates = [
        SCRIPT_DIR / "Spectral-Bold.ttf",
        Path("C:/Windows/Fonts/timesbd.ttf"),
        Path("C:/Windows/Fonts/georgiab.ttf"),
        Path("C:/Windows/Fonts/times.ttf"),
    ]
    for font_path in candidates:
        if font_path.exists():
            return str(font_path)
    return "timesbd.ttf"


def ensure_source_video():
    """Ensure source.mp4 exists; download if necessary."""
    if SOURCE_VIDEO.exists() and SOURCE_VIDEO.stat().st_size > 100000:
        return
    print(f"[!] source.mp4 not found. Downloading from {SOURCE_URL}...")
    import urllib.request
    urllib.request.urlretrieve(SOURCE_URL, str(SOURCE_VIDEO))
    print(f"[+] Download complete: {SOURCE_VIDEO}")


def build_clean_plate(crop: np.ndarray) -> np.ndarray:
    """Remove old baked-in letters using adaptive threshold + inpaint."""
    h, w, _ = crop.shape
    roi_gray = cv2.cvtColor(crop, cv2.COLOR_BGR2GRAY)
    text_box = roi_gray[70:238, 55:195]
    thresh = cv2.adaptiveThreshold(
        text_box, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY_INV, 15, 6
    )

    text_mask = np.zeros((h, w), dtype=np.uint8)
    text_mask[70:238, 55:195] = thresh
    kernel = cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (3, 3))
    text_mask = cv2.dilate(text_mask, kernel, iterations=3)

    return cv2.inpaint(crop, text_mask, 5, cv2.INPAINT_TELEA)


def carve_text_on_stone(clean_plate: np.ndarray, lines: list[str], font_file: str) -> np.ndarray:
    """Engrave 3 lines of text into the clean stone face."""
    h, w, _ = clean_plate.shape
    text_img = Image.new("L", (w, h), 0)
    draw = ImageDraw.Draw(text_img)

    center_x = 124  # Center axis of the stone face
    y_positions = [105, 140, 174]

    for i, line in enumerate(lines[:3]):
        # Auto-fit font size to stay within stone margin (<= 126px width)
        best_font = None
        for sz in range(16, 8, -1):
            f = ImageFont.truetype(font_file, sz)
            bbox = draw.textbbox((0, 0), line, font=f)
            tw = bbox[2] - bbox[0]
            if tw <= 126:
                best_font = f
                break
        if best_font is None:
            best_font = ImageFont.truetype(font_file, 10)

        bbox = draw.textbbox((0, 0), line, font=best_font)
        tw = bbox[2] - bbox[0]
        x = int(center_x - tw / 2)
        y = y_positions[i]
        draw.text((x, y), line, font=best_font, fill=255)

    alpha_text = np.array(text_img, dtype=np.float32) / 255.0

    # Authentic stone carving optics:
    # Inner shadow (light source is from top-left, shadow falls to bottom-right)
    shadow_mask = np.roll(np.roll(alpha_text, 1, axis=0), 1, axis=1)
    shadow_inner = np.maximum(0, shadow_mask - alpha_text)

    # Bevel highlight on bottom-right edge
    highlight_mask = np.roll(np.roll(alpha_text, -1, axis=0), -1, axis=1)
    highlight_edge = np.maximum(0, highlight_mask - alpha_text)

    # Soft anti-aliased incision mask
    alpha_blur = cv2.GaussianBlur(alpha_text, (3, 3), 0.4)

    # Stone grain matching the surface noise
    np.random.seed(42)
    grain = (np.random.randn(h, w, 3) * 6).astype(np.float32)

    # Dark chiseled groove color
    incision = (clean_plate.astype(np.float32) * 0.38) + grain

    carved = clean_plate.astype(np.float32).copy()
    for c in range(3):
        carved[:, :, c] = carved[:, :, c] * (1.0 - alpha_blur) + incision[:, :, c] * alpha_blur
        carved[:, :, c] -= shadow_inner * 22
        carved[:, :, c] += highlight_edge * 32

    return np.clip(carved, 0, 255).astype(np.uint8)


def process_all_error_videos():
    ensure_source_video()
    PUBLIC_ERRORS_DIR.mkdir(parents=True, exist_ok=True)
    PUBLIC_MEDIA_DIR.mkdir(parents=True, exist_ok=True)
    ffmpeg_bin = find_ffmpeg()
    font_file = get_font_path()

    cap = cv2.VideoCapture(str(SOURCE_VIDEO))
    if not cap.isOpened():
        raise RuntimeError(f"Could not open source video: {SOURCE_VIDEO}")

    width = int(cap.get(cv2.CAP_PROP_FRAME_WIDTH))
    height = int(cap.get(cv2.CAP_PROP_FRAME_HEIGHT))
    fps = cap.get(cv2.CAP_PROP_FPS) or 24.0
    total_frames = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))

    print(f"[*] Source Video: {width}x{height} @ {fps:.2f}fps ({total_frames} frames)")
    print(f"[*] Generating videos for codes: {list(ERROR_QUOTES.keys())}")

    # Read frame 0
    ret, frame_0 = cap.read()
    if not ret:
        raise RuntimeError("Failed to read initial frame from source video.")

    crop_0 = frame_0[CROP_Y1:CROP_Y2, CROP_X1:CROP_X2].copy()
    clean_plate = build_clean_plate(crop_0)

    # Pre-build carved patches for all error codes
    carved_patches = {}
    h, w, _ = crop_0.shape
    patch_mask = np.zeros((h, w), dtype=np.float32)
    patch_mask[78:232, 65:188] = 1.0
    patch_mask = cv2.GaussianBlur(patch_mask, (19, 19), 6.0)
    patch_mask_3c = np.repeat(patch_mask[:, :, np.newaxis], 3, axis=2)

    for code, lines in ERROR_QUOTES.items():
        carved_crop = carve_text_on_stone(clean_plate, lines, font_file)
        carved_patches[code] = carved_crop.astype(np.float32)

    f0_roi = crop_0.astype(np.float32)

    # Open ffmpeg pipes for each error code
    pipes = {}
    mp4_paths = {}
    for code in ERROR_QUOTES.keys():
        out_mp4 = PUBLIC_ERRORS_DIR / f"bg-{code}.mp4"
        mp4_paths[code] = out_mp4
        mp4_cmd = [
            ffmpeg_bin,
            "-y",
            "-f", "rawvideo",
            "-vcodec", "rawvideo",
            "-s", f"{width}x{height}",
            "-pix_fmt", "bgr24",
            "-r", str(fps),
            "-i", "-",
            "-c:v", "libx264",
            "-crf", "24",
            "-preset", "slow",
            "-pix_fmt", "yuv420p",
            "-movflags", "+faststart",
            "-an",
            str(out_mp4)
        ]
        pipes[code] = subprocess.Popen(mp4_cmd, stdin=subprocess.PIPE, stderr=subprocess.PIPE)

    def write_frames_for_code(frame: np.ndarray, is_first: bool = False):
        roi_orig = frame[CROP_Y1:CROP_Y2, CROP_X1:CROP_X2].astype(np.float32)
        diff_bright = np.maximum(0, roi_orig - f0_roi)
        diff_val = np.mean(diff_bright, axis=2)
        particle_weight = np.clip((diff_val - 15.0) / 30.0, 0.0, 1.0)[:, :, np.newaxis]

        for code, pipe in pipes.items():
            carved_f = carved_patches[code]
            blended_roi = roi_orig * (1.0 - patch_mask_3c) + carved_f * patch_mask_3c
            final_roi = blended_roi * (1.0 - particle_weight) + roi_orig * particle_weight

            code_frame = frame.copy()
            code_frame[CROP_Y1:CROP_Y2, CROP_X1:CROP_X2] = np.clip(final_roi, 0, 255).astype(np.uint8)

            if is_first:
                poster_path = PUBLIC_ERRORS_DIR / f"poster-{code}.webp"
                rgb_f0 = cv2.cvtColor(code_frame, cv2.COLOR_BGR2RGB)
                poster_pil = Image.fromarray(rgb_f0)
                poster_pil.save(str(poster_path), "WEBP", quality=88)

            pipe.stdin.write(code_frame.tobytes())

    print("[*] Processing frames and writing to video pipes...")
    write_frames_for_code(frame_0, is_first=True)

    frame_idx = 1
    while True:
        ret, frame = cap.read()
        if not ret:
            break
        write_frames_for_code(frame)
        frame_idx += 1
        if frame_idx % 30 == 0 or frame_idx == total_frames:
            print(f"    Frame {frame_idx}/{total_frames} ({frame_idx/total_frames*100:.1f}%)")

    cap.release()

    # Close and finalize all MP4 pipes
    for code, pipe in pipes.items():
        pipe.stdin.close()
        _, stderr_data = pipe.communicate()
        if pipe.returncode != 0:
            print(f"[!] Warning: MP4 encode error for {code}: {stderr_data.decode('utf-8', errors='ignore')}")

    print("[+] All MP4 videos created. Generating WebM (VP9) versions...")
    for code, mp4_file in mp4_paths.items():
        webm_file = PUBLIC_ERRORS_DIR / f"bg-{code}.webm"
        webm_cmd = [
            ffmpeg_bin,
            "-y",
            "-i", str(mp4_file),
            "-c:v", "libvpx-vp9",
            "-crf", "30",
            "-b:v", "0",
            "-an",
            str(webm_file)
        ]
        subprocess.run(webm_cmd, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        print(f"    [+] {code}: MP4 ({mp4_file.stat().st_size / (1024*1024):.2f} MB), WebM ({webm_file.stat().st_size / (1024*1024):.2f} MB)")

    # Copy 404 as fallback default to public/media/error-bg.*
    shutil.copy2(PUBLIC_ERRORS_DIR / "bg-404.mp4", PUBLIC_MEDIA_DIR / "error-bg.mp4")
    shutil.copy2(PUBLIC_ERRORS_DIR / "bg-404.webm", PUBLIC_MEDIA_DIR / "error-bg.webm")
    shutil.copy2(PUBLIC_ERRORS_DIR / "poster-404.webp", PUBLIC_MEDIA_DIR / "error-bg-poster.webp")

    print("\n" + "=" * 60)
    print("[OK] ALL ERROR BACKGROUND ASSETS COMPLETED:")
    for code in ERROR_QUOTES.keys():
        print(f"    - {code}: bg-{code}.mp4 | bg-{code}.webm | poster-{code}.webp")
        print(f"      Hikmat: {' / '.join(ERROR_QUOTES[code])}")
    print("=" * 60)


if __name__ == "__main__":
    process_all_error_videos()
