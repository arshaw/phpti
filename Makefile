
VER = `cat version.txt`
VER_SED = sed s/@VERSION/"${VER}"/
DATE = `git log -1 | grep Date: | sed 's/[^:]*: *//'`
DATE_SED = sed s/@DATE/"${DATE}"/

zip:
	@rm -rf build/*
	@mkdir -p build
	@mkdir -p dist
	@cat src/ti.php \
		| ${VER_SED} \
		| ${DATE_SED} \
		> build/ti.php
	@cp readme.mkd build/readme.txt
	@cp license.txt build
	@cd build; zip -q phpti.zip *
	@mv build/phpti.zip dist/phpti-${VER}.zip
	
clean:
	@rm -rf build/*
	@rm -rf dist/*
