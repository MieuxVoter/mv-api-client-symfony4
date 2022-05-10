#!/bin/sh

# I know it's not friendly to log anything here,
# but we have weird logs:
#   [SETTING UP STORAGE /var/log/nginx/error.log] {0} @ {0/s}
#   Cleaning up resources...
# and I don't know where they are coming from.
# The STORAGE one is extra confusing because we also configured
# goaccess to read both from access.log AND error.log
# We also do not use persistence (storage?) for now.
/bin/goaccess --version

# We're in a busybox:musl so
#zcat --force /var/log/nginx/*.log* | /bin/goaccess - $@
# > zcat: no gzip/bzip2/xz magic
# and no "apk add" either to install the package

# How are we going to get our archived logs?
# Maybe we'll need a Dockerfile in the end…
# Let's just pass things to the previous entrypoint for now
#/bin/goaccess $@

# By now we have our own container (alpine) so we installed gzip
zcat --force /var/log/nginx/*.log* | /bin/goaccess - $@

# This clarifies the exit 0 of the container in the docker logs.
echo -e "GoAccess has completed generating the HTML report.   Exiting…"
