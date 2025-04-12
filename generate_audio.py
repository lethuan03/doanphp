import sys
from gtts import gTTS

def text_to_audio(text_file, output_audio):
    # Đọc văn bản từ file
    with open(text_file, 'r', encoding='utf-8') as f:
        text = f.read()

    # Chuyển văn bản thành audio
    tts = gTTS(text=text, lang='vi')  # Ngôn ngữ tiếng Việt
    tts.save(output_audio)

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python generate_audio.py <text_file> <output_audio>")
        sys.exit(1)

    text_file = sys.argv[1]
    output_audio = sys.argv[2]
    text_to_audio(text_file, output_audio)