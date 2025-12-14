#!/bin/bash
#
# WP4BD Branch Consolidation Script
# Merges upbeat-khorana and claude/setup-testing-environment into main
# Skips composer-refactor (keeping as separate experimental branch)
#
# Usage: ./merge-all-branches.sh
#

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=====================================${NC}"
echo -e "${BLUE}WP4BD Branch Consolidation Script${NC}"
echo -e "${BLUE}=====================================${NC}"
echo ""

# Function to print colored status messages
print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

# Check we're in the right directory
if [ ! -f "README.md" ] || [ ! -d "backdrop-1.30" ]; then
    print_error "This doesn't look like the WP4BD repository root"
    print_error "Please run this script from: /Users/lukemccormick/Sites/BACKDROP/WP4BD"
    exit 1
fi

print_status "Found WP4BD repository"

# Check for uncommitted changes
if ! git diff-index --quiet HEAD --; then
    print_error "You have uncommitted changes. Please commit or stash them first."
    echo ""
    echo "Run one of these:"
    echo "  git stash              # Temporarily save changes"
    echo "  git add . && git commit -m 'WIP'  # Commit changes"
    exit 1
fi

print_status "Working directory is clean"

# Fetch latest from origin
print_info "Fetching latest from GitHub..."
git fetch origin

print_status "Fetched latest remote branches"

# Check current branch
CURRENT_BRANCH=$(git branch --show-current)
if [ "$CURRENT_BRANCH" != "main" ]; then
    print_warning "Currently on branch: $CURRENT_BRANCH"
    echo -e "${YELLOW}Switching to main...${NC}"
    git checkout main
fi

print_status "On main branch"

# Show current state
echo ""
echo -e "${BLUE}Current branch status:${NC}"
git log --oneline -3
echo ""

# Check if branches exist
echo -e "${BLUE}Checking branches to merge...${NC}"

BRANCHES_TO_MERGE=()

# Check upbeat-khorana
if git show-ref --verify --quiet refs/remotes/origin/upbeat-khorana; then
    print_status "Found origin/upbeat-khorana"
    BRANCHES_TO_MERGE+=("origin/upbeat-khorana")
else
    print_warning "origin/upbeat-khorana not found on remote"
    if git show-ref --verify --quiet refs/heads/upbeat-khorana; then
        print_info "Found local upbeat-khorana branch"
        BRANCHES_TO_MERGE+=("upbeat-khorana")
    fi
fi

# Check claude/setup-testing-environment
if git show-ref --verify --quiet refs/remotes/origin/claude/setup-testing-environment-01XU73r6DXHviqs3JEQdEMxr; then
    print_status "Found origin/claude/setup-testing-environment-01XU73r6DXHviqs3JEQdEMxr"
    BRANCHES_TO_MERGE+=("origin/claude/setup-testing-environment-01XU73r6DXHviqs3JEQdEMxr")
else
    print_warning "Claude testing environment branch not found"
fi

# Show what will be merged
echo ""
echo -e "${BLUE}=====================================${NC}"
echo -e "${BLUE}MERGE PLAN${NC}"
echo -e "${BLUE}=====================================${NC}"
echo ""
echo "The following branches will be merged into main:"
for branch in "${BRANCHES_TO_MERGE[@]}"; do
    echo "  • $branch"
done
echo ""
echo -e "${YELLOW}Branches being SKIPPED (per your decision):${NC}"
echo "  • composer-refactor (keep as experimental branch)"
echo "  • dec12-architecture-migration (redundant with upbeat-khorana)"
echo ""

# Ask for confirmation
read -p "Do you want to proceed with these merges? (y/N) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_info "Merge cancelled by user"
    exit 0
fi

echo ""
echo -e "${BLUE}=====================================${NC}"
echo -e "${BLUE}EXECUTING MERGES${NC}"
echo -e "${BLUE}=====================================${NC}"
echo ""

# Merge counter
MERGE_COUNT=0
MERGE_FAILED=0

# Function to attempt merge
attempt_merge() {
    local branch=$1
    local message=$2

    echo ""
    print_info "Merging $branch..."

    if git merge "$branch" -m "$message" --no-edit; then
        print_status "Successfully merged $branch"
        ((MERGE_COUNT++))

        # Show what changed
        echo -e "${BLUE}Files changed:${NC}"
        git diff --name-only HEAD~1 HEAD | head -10
        echo ""
    else
        print_error "Merge conflict detected in $branch"
        ((MERGE_FAILED++))

        echo ""
        echo -e "${YELLOW}Conflicted files:${NC}"
        git diff --name-only --diff-filter=U
        echo ""
        echo -e "${YELLOW}To resolve:${NC}"
        echo "  1. Fix conflicts in the files listed above"
        echo "  2. Run: git add <resolved-files>"
        echo "  3. Run: git commit"
        echo "  4. Re-run this script to continue remaining merges"
        echo ""

        return 1
    fi
}

# Merge upbeat-khorana
for branch in "${BRANCHES_TO_MERGE[@]}"; do
    case "$branch" in
        *upbeat-khorana)
            if ! attempt_merge "$branch" "Merge upbeat-khorana: Add WordPress DB dump and integration planning docs"; then
                print_warning "Stopping merges due to conflict. Resolve and re-run script."
                exit 1
            fi
            ;;
        *setup-testing-environment*)
            if ! attempt_merge "$branch" "Merge testing environment setup: Add MariaDB configuration and documentation"; then
                print_warning "Stopping merges due to conflict. Resolve and re-run script."
                exit 1
            fi
            ;;
    esac
done

# Summary
echo ""
echo -e "${BLUE}=====================================${NC}"
echo -e "${BLUE}MERGE SUMMARY${NC}"
echo -e "${BLUE}=====================================${NC}"
echo ""
print_status "Successfully merged $MERGE_COUNT branch(es)"

if [ $MERGE_FAILED -gt 0 ]; then
    print_warning "$MERGE_FAILED merge(s) had conflicts"
fi

echo ""
echo -e "${BLUE}Current main branch status:${NC}"
git log --oneline -5
echo ""

# Ask about pushing
echo -e "${BLUE}=====================================${NC}"
echo -e "${BLUE}PUSH TO GITHUB${NC}"
echo -e "${BLUE}=====================================${NC}"
echo ""
print_info "Merges completed locally. Ready to push to GitHub."
echo ""
read -p "Push main branch to origin? (y/N) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_info "Pushing to origin/main..."
    if git push origin main; then
        print_status "Successfully pushed to GitHub"
    else
        print_error "Push failed. You may need to pull first or resolve push conflicts."
        echo ""
        echo "Try: git pull origin main --rebase"
        exit 1
    fi
else
    print_info "Skipped push. You can push later with: git push origin main"
fi

# Optional cleanup
echo ""
echo -e "${BLUE}=====================================${NC}"
echo -e "${BLUE}OPTIONAL CLEANUP${NC}"
echo -e "${BLUE}=====================================${NC}"
echo ""
print_info "Now that branches are merged, you can optionally delete them."
echo ""
echo "To delete local branches:"
echo "  git branch -d upbeat-khorana"
echo "  git branch -d dec12-architecture-migration"
echo ""
echo "To delete remote branches (CAUTION - permanent!):"
echo "  git push origin --delete upbeat-khorana"
echo "  git push origin --delete dec12-architecture-migration"
echo ""
print_warning "Keeping composer-refactor branch (per your decision)"
echo ""

# Final status
echo -e "${GREEN}=====================================${NC}"
echo -e "${GREEN}CONSOLIDATION COMPLETE!${NC}"
echo -e "${GREEN}=====================================${NC}"
echo ""
print_status "All branches successfully merged into main"
print_status "Your main branch now contains:"
echo "  • WordPress 4.9 database dump"
echo "  • Backdrop database dump"
echo "  • All migration planning documentation"
echo "  • WordPress core integration plans"
echo "  • Testing environment setup guide"
echo "  • Latest WP4BD code (WP4BD-012, WP4BD-013)"
echo ""
print_info "Next steps:"
echo "  1. Test the merged main branch"
echo "  2. Delete redundant branches (optional)"
echo "  3. Continue development on main"
echo ""
