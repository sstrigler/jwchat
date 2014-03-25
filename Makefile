
all: install

po:
	@./scripts/extract-message-catalog.pl

install:
	@if [ ! -d ./htdocs ]; then \
		echo ""; \
		mkdir ./htdocs; \
		echo "Copying Stylesheets ..."; \
		cp ./src/*.css ./htdocs; \
		echo "Copying Images ...";\
		cp -r ./src/images ./htdocs;\
		echo "Copying Sounds ...";\
		cp -r ./src/sounds htdocs;\
		echo ""; \
	fi
	@./scripts/templateparser.pl;
	@echo "Copying static files ...";
	@	./scripts/cpstatic.sh;
	@echo "Copying additional libs ...";
	@	./scripts/cplibs.sh;

clean:
	@rm -r ./htdocs
	@rm -r ./htdocs.*

.PHONY: clean po

