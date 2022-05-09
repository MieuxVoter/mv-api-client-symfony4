#!/bin/sh

# We're in a busybox:musl so
# zcat: no gzip/bzip2/xz magic
#zcat --force /var/log/nginx/*.log* | /bin/goaccess - $@
# and no "apk add" either to install the package

# How are we going to get our archived logs?
# Maybe we'll need a Dockerfile in the end…
# If we do we can probably remove this entrypoint.

# Let's just pass things to the previous entrypoint for now
/bin/goaccess $@
