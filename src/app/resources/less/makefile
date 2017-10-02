NODE = /usr/bin/nodejs
LESS = /usr/local/bin/lessc
IN = admin.less
OUT = ../admin.css
MIN-OUT = ../admin.min.css

build:
	$(NODE) $(LESS) $(IN) $(OUT)
	# /usr/bin/nodejs /usr/local/bin/lessc admin.less

min:
	$(NODE) $(LESS) --clean-css $(IN) $(MIN-OUT)
	
clean:
	rm -f *.css

rebuild: clean build min

