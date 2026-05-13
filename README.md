````md
# Staggered Text Layout for Kindle

A simple PHP/HTML/JavaScript tool that converts `.html` and `.txt` books into a staggered plain text layout
designed for easier diagonal reading on Kindle and other e-readers.

The app loads book files from the `./libros/` folder, lets you choose a book,
configure the staggered column structure, and export the result as a `.txt` file.

## Features

- Loads `.html` and `.txt` files automatically from `./libros/`
- Exports books as plain `.txt`
- Configurable staggered layout
- Supports 3 to 10 columns
- Each column can have its own:
  - number of lines
  - words per line
- Configurable spacing between columns
- Optional empty line between text blocks
- Monospace-friendly output for Kindle reading
- Works locally with a simple PHP server

## Example output

```txt
Hello there
            how are you today
                              yes
                              here
                                   walking
                                   slowly

https://vernetit.github.io/staggered-kindle-reader/
