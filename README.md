# PHP SMTP Server

So this is an SMTP server written in PHP, a language wholly unsuited for such a task. I just did it to find out whether I could.
I've implemented it with reference to RFC 2821 but I skipped over bits that looked hard so don't expect this to work to the spec :p

The server doesn't attempt to deal with sending emails at all, so you can't use it as a relay.

This also only supports normal, ye olde SMTP rather than the more modern ESMTP. I'll add support gradually I think. First though SMTP needs to be done completely.

There is no config file, just configure it by editing anything you want to change. I suggest a Repository subclass to save messages where / how you want.

oh and finally

## Don't use this in production!