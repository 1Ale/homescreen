# Home

A Nextcloud 34 app that shows the same apps as the header app menu, as a full-page grid.

Put this folder in `custom_apps/homescreen` (the folder name must match the app id) and enable it:

```bash
sudo -u www-data php occ app:enable homescreen
```

Optional — first screen after login:

```bash
sudo -u www-data php occ config:system:set defaultapp --value="homescreen"
```

AGPL-3.0-or-later. See [LICENSE](LICENSE).
