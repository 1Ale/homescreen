#!/bin/sh
# SPDX-FileCopyrightText: 2026 Alexandre Luvizotti Lopes
# SPDX-License-Identifier: AGPL-3.0-or-later
#
# Package the app, sign the .tar.gz, and publish it to the App Store.
#
# The store does not accept the file itself: it downloads an HTTPS URL,
# checks the SHA512 signature (key in ~/.nextcloud/certificates/), and
# reads info.xml from the archive. That is why "publish" uploads the
# .tar.gz as a GitHub Release, then POSTs /api/v1/apps/releases.
#
# Usage:
#   ./release.sh           # write build/$APP.tar.gz + .sig
#   ./release.sh publish   # GitHub Release + App Store registration
#
# Needs:
#   ~/.nextcloud/certificates/$APP.key
#   ~/.nextcloud/appstore.token   (or $NC_APPSTORE_TOKEN)
#   gh authenticated (publish only)
#   https://nextcloudappstore.readthedocs.io/en/latest/developer.html

set -eu
cd "$(dirname "$0")"

APP=$(sed -n 's/.*<id>\(.*\)<\/id>.*/\1/p' appinfo/info.xml)
VERSION=$(sed -n 's/.*<version>\(.*\)<\/version>.*/\1/p' appinfo/info.xml)
KEY="$HOME/.nextcloud/certificates/$APP.key"
ARCHIVE="build/$APP.tar.gz"
TAG="v$VERSION"

mkdir -p build
rm -f "$ARCHIVE" "$ARCHIVE.sig"

echo "Packaging $APP $VERSION → $ARCHIVE"
git archive --format=tar.gz --prefix="$APP/" HEAD -o "$ARCHIVE"
tar -tzf "$ARCHIVE" | grep -qx "$APP/appinfo/info.xml"

echo "Signing"
test -f "$KEY" || { echo "Missing key: $KEY" >&2; exit 1; }
SIGNATURE=$(openssl dgst -sha512 -sign "$KEY" "$ARCHIVE" | openssl base64 | tr -d '\n')
printf '%s\n' "$SIGNATURE" > "$ARCHIVE.sig"
echo "Signature written to $ARCHIVE.sig"

if [ "${1:-}" != publish ]; then
	echo "Done. To publish: ./release.sh publish"
	exit 0
fi

if [ -n "$(git status --porcelain)" ]; then
	echo "Commit your changes before publishing (the package is built from HEAD)." >&2
	exit 1
fi

TOKEN="${NC_APPSTORE_TOKEN:-}"
if [ -z "$TOKEN" ] && [ -f "$HOME/.nextcloud/appstore.token" ]; then
	TOKEN=$(cat "$HOME/.nextcloud/appstore.token")
fi
test -n "$TOKEN" || { echo "Missing App Store token: ~/.nextcloud/appstore.token or \$NC_APPSTORE_TOKEN" >&2; exit 1; }

command -v gh >/dev/null || { echo "Install the GitHub CLI (gh)." >&2; exit 1; }

echo "GitHub Release $TAG"
if gh release view "$TAG" >/dev/null 2>&1; then
	gh release upload "$TAG" "$ARCHIVE" --clobber
else
	gh release create "$TAG" "$ARCHIVE" --title "$APP $VERSION" --generate-notes
fi

REPO=$(gh repo view --json nameWithOwner --jq .nameWithOwner)
DOWNLOAD="https://github.com/$REPO/releases/download/$TAG/$APP.tar.gz"

echo "App Store ← $DOWNLOAD"
curl -sS -X POST https://apps.nextcloud.com/api/v1/apps/releases \
	-H "Authorization: Token $TOKEN" \
	-H "Content-Type: application/json" \
	-d "{\"download\":\"$DOWNLOAD\",\"signature\":\"$SIGNATURE\"}" \
	-w "\nHTTP %{http_code}\n"
