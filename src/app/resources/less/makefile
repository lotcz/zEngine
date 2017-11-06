NODE = nodejs
LESS = /usr/bin/lessc
IN = admin.less
OUT = ../admin.css
MIN-OUT = ../admin.min.css

build:
	$(NODE) $(LESS) $(IN) $(OUT)

min:
	$(NODE) $(LESS) --clean-css $(IN) $(MIN-OUT)
	
clean:
	rm -f *.css

rebuild: clean build min

