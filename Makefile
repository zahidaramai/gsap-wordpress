# Makefile for GSAP WordPress Plugin
# Requires: PHP 8.1+, Composer, Node.js 18+, npm

.PHONY: help install test lint fix clean build deploy

# Colors for output
GREEN  := $(shell tput -Txterm setaf 2)
YELLOW := $(shell tput -Txterm setaf 3)
WHITE  := $(shell tput -Txterm setaf 7)
RESET  := $(shell tput -Txterm sgr0)

# Default target
.DEFAULT_GOAL := help

## help: Display this help message
help:
	@echo ''
	@echo '${GREEN}GSAP for WordPress - Development Tasks${RESET}'
	@echo ''
	@echo '${YELLOW}Usage:${RESET}'
	@echo '  ${GREEN}make${RESET} ${YELLOW}<target>${RESET}'
	@echo ''
	@echo '${YELLOW}Targets:${RESET}'
	@awk 'BEGIN {FS = ":.*?## "} { \
		if (/^[a-zA-Z_-]+:.*?##.*$$/) {printf "  ${GREEN}%-15s${RESET} %s\n", $$1, $$2} \
		else if (/^## .*$$/) {printf "  ${YELLOW}%s${RESET}\n", substr($$1,4)} \
		}' $(MAKEFILE_LIST)

## install: Install all dependencies
install: ## Install PHP and Node dependencies
	@echo "${GREEN}Installing Composer dependencies...${RESET}"
	composer install
	@echo "${GREEN}Installing Node dependencies...${RESET}"
	npm install
	@echo "${GREEN}✓ All dependencies installed${RESET}"

## test: Run all tests and checks
test: ## Run PHPCS, PHPStan, ESLint, and Stylelint
	@echo "${GREEN}Running PHP CodeSniffer...${RESET}"
	composer phpcs
	@echo "${GREEN}Running PHPStan...${RESET}"
	composer phpstan
	@echo "${GREEN}Running ESLint...${RESET}"
	npm run lint:js
	@echo "${GREEN}Running Stylelint...${RESET}"
	npm run lint:css
	@echo "${GREEN}✓ All tests passed${RESET}"

## lint: Run all linting tools
lint: ## Run all linting (alias for test)
	@make test

## lint-php: Run PHP linting only
lint-php: ## Run PHPCS and PHPStan
	@echo "${GREEN}Running PHP CodeSniffer...${RESET}"
	composer phpcs
	@echo "${GREEN}Running PHPStan...${RESET}"
	composer phpstan

## lint-js: Run JavaScript linting
lint-js: ## Run ESLint
	@echo "${GREEN}Running ESLint...${RESET}"
	npm run lint:js

## lint-css: Run CSS linting
lint-css: ## Run Stylelint
	@echo "${GREEN}Running Stylelint...${RESET}"
	npm run lint:css

## fix: Auto-fix all fixable issues
fix: ## Auto-fix PHP, JS, and CSS issues
	@echo "${GREEN}Auto-fixing PHP issues...${RESET}"
	composer phpcbf
	@echo "${GREEN}Auto-fixing JavaScript issues...${RESET}"
	npm run fix:js
	@echo "${GREEN}Auto-fixing CSS issues...${RESET}"
	npm run fix:css
	@echo "${GREEN}Formatting all files...${RESET}"
	npm run format
	@echo "${GREEN}✓ All auto-fixes applied${RESET}"

## fix-php: Auto-fix PHP issues
fix-php: ## Auto-fix PHP coding standards
	@echo "${GREEN}Auto-fixing PHP issues...${RESET}"
	composer phpcbf

## fix-js: Auto-fix JavaScript issues
fix-js: ## Auto-fix JavaScript with ESLint
	@echo "${GREEN}Auto-fixing JavaScript issues...${RESET}"
	npm run fix:js

## fix-css: Auto-fix CSS issues
fix-css: ## Auto-fix CSS with Stylelint
	@echo "${GREEN}Auto-fixing CSS issues...${RESET}"
	npm run fix:css

## format: Format all files with Prettier
format: ## Format JS, CSS, JSON, and MD files
	@echo "${GREEN}Formatting all files...${RESET}"
	npm run format

## clean: Clean up generated files
clean: ## Remove vendor, node_modules, and cache files
	@echo "${GREEN}Cleaning up...${RESET}"
	rm -rf vendor/
	rm -rf node_modules/
	rm -rf .phpcs-cache
	rm -f composer.lock
	rm -f package-lock.json
	@echo "${GREEN}✓ Cleanup complete${RESET}"

## clean-cache: Clear all caches
clean-cache: ## Clear PHPCS and other caches
	@echo "${GREEN}Clearing caches...${RESET}"
	rm -rf .phpcs-cache
	@echo "${GREEN}✓ Caches cleared${RESET}"

## build: Create production build
build: ## Run tests and prepare for deployment
	@echo "${GREEN}Building plugin...${RESET}"
	@make test
	@echo "${GREEN}✓ Build complete${RESET}"

## build-zip: Create deployable ZIP file
build-zip: ## Create plugin ZIP file
	@echo "${GREEN}Creating plugin ZIP...${RESET}"
	@rm -f gsap-for-wordpress.zip
	@zip -r gsap-for-wordpress.zip . \
		-x "*.git*" \
		-x "*node_modules/*" \
		-x "*vendor/*" \
		-x "*.DS_Store" \
		-x "*Makefile" \
		-x "*.editorconfig" \
		-x "*phpcs.xml" \
		-x "*phpstan.neon" \
		-x "*.eslintrc.json" \
		-x "*.stylelintrc.json" \
		-x "*composer.json" \
		-x "*composer.lock" \
		-x "*package.json" \
		-x "*package-lock.json"
	@echo "${GREEN}✓ ZIP created: gsap-for-wordpress.zip${RESET}"

## watch: Watch files for changes (if configured)
watch: ## Watch and auto-fix on file changes
	@echo "${GREEN}Watching for changes...${RESET}"
	@echo "${YELLOW}Note: This requires additional setup${RESET}"

## info: Display environment information
info: ## Show PHP, Composer, Node, and npm versions
	@echo "${GREEN}Environment Information:${RESET}"
	@echo "${YELLOW}PHP:${RESET}"
	@php -v | head -1
	@echo "${YELLOW}Composer:${RESET}"
	@composer --version
	@echo "${YELLOW}Node:${RESET}"
	@node -v
	@echo "${YELLOW}npm:${RESET}"
	@npm -v
	@echo "${YELLOW}WordPress Requirements:${RESET}"
	@echo "  Minimum: 6.7"
	@echo "  PHP Minimum: 8.1"
