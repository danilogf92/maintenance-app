"""Render controlled HTML. External network and filesystem URLs are forbidden."""
import pathlib
import sys
from weasyprint import HTML, default_url_fetcher


def embedded_images_only(url, *args, **kwargs):
    if not url.startswith(("data:image/png;base64,", "data:image/jpeg;base64,", "data:image/webp;base64,")):
        raise ValueError("Only embedded raster images are permitted")
    return default_url_fetcher(url, *args, **kwargs)


def main():
    source, destination = map(pathlib.Path, sys.argv[1:3])
    HTML(string=source.read_text(encoding="utf-8"), url_fetcher=embedded_images_only).write_pdf(str(destination))
    if destination.read_bytes()[:5] != b"%PDF-":
        raise RuntimeError("Invalid PDF output")


if __name__ == "__main__":
    main()
