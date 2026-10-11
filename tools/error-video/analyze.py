import cv2
import numpy as np

# Load frames
f0 = cv2.imread('tools/error-video/frame_000.png')
f_mid = cv2.imread('tools/error-video/frame_mid.png')
f_last = cv2.imread('tools/error-video/frame_last.png')

h, w, _ = f0.shape
print(f"Dimensions: {w}x{h}")

# The stone is in bottom-left corner
# Let's inspect approximately y: 650 to 950, x: 50 to 350
# Let's crop stone ROI
stone_roi_0 = f0[650:950, 50:350]
stone_roi_mid = f_mid[650:950, 50:350]
stone_roi_last = f_last[650:950, 50:350]

# Calculate difference
diff_mid = cv2.absdiff(stone_roi_0, stone_roi_mid)
diff_last = cv2.absdiff(stone_roi_0, stone_roi_last)

mean_diff_mid = np.mean(diff_mid)
mean_diff_last = np.mean(diff_last)

print(f"Mean diff (0 vs mid): {mean_diff_mid:.4f}")
print(f"Mean diff (0 vs last): {mean_diff_last:.4f}")

# Save the stone crop from frame 0 to inspect exact text coordinates
cv2.imwrite('tools/error-video/stone_crop.png', stone_roi_0)
print("Saved stone_roi_0 to tools/error-video/stone_crop.png")
