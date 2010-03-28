
VER = `cat version.txt`
VER_SED = sed s/@VERSION/"${VER}"/
DATE = `git log -1 | grep Date: | sed 's/[^:]*: *//'`
DATE_SED = sed s/@DATE/"${DATE}"/

zip:
	@rm -rf build/*
	
	@echo "building..."
	@mkdir -p build
	@mkdir -p dist
	@cat src/ti.php \
		| ${VER_SED} \
		| ${DATE_SED} \
		| sed /require.*debug\.php/d \
		> build/ti.php
	@cp readme.mkd build/readme.txt
	@cp license.txt build
	
	@echo "running tests..."
	@if tests/run-tests -B; then \
		echo "zipping..."; \
		cd build; zip -q phpti.zip *; cd ..; \
		mv build/phpti.zip dist/phpti-${VER}.zip; \
	else \
		echo "DID NOT PASS ALL TESTS."; \
	fi
	
clean:
	@rm -rf build/*
	@rm -rf dist/*
