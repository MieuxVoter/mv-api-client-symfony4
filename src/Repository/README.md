No database, so no repositories.

Perhaps at some point glue the repo services directly on the OAS,
(or a cache of it, which makes it even more work)
but initial review of this looks like a lot of work.

Besides, this app may have a database of its own at some point,
and it may well be handled by Doctrine, so we'll have some repos.
