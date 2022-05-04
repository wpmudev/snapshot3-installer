To get the working `build/installer.php`:

	npm install
	node_modules/.bin/grunt build

To actually restore an archive, we need 2 things:

1) Built `snapshot-installer.php`
2) Actual backup archive :)

Once you have both, copy them over to a destination directory of your choice
(within your webroot, of course). The installer will expect the backup archive
in the same directory, and named after a Snapshot v4 or Snapshot v3 full backup archive
file convention (i.e. `[0-9a-f]{12}\.zip` or `full_*.zip`).