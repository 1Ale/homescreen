# Home

A Nextcloud 34 and 35 app that shows the same apps as the header app menu, as a full-page grid.

Put this folder in `custom_apps/homescreen` (the folder name must match the app id) and enable it:

```bash
sudo -u www-data php occ app:enable homescreen
```

Optional — first screen after login:

```bash
sudo -u www-data php occ config:system:set defaultapp --value="homescreen"
```

## Publishing a release

`./release.sh` packages the app into `build/` (gitignored), signs it, and with `publish` uploads the `.tar.gz` as a GitHub Release and registers it on the [App Store](https://apps.nextcloud.com).

```bash
./release.sh           # write build/homescreen.tar.gz and the signature
./release.sh publish   # GitHub Release + POST to the store
```

Before `publish`: version committed in `appinfo/info.xml`, key in `~/.nextcloud/certificates/homescreen.key`, store token in `~/.nextcloud/appstore.token` ([account/token](https://apps.nextcloud.com/account/token)), `gh` authenticated.

AGPL-3.0-or-later. See [LICENSE](LICENSE).
