# Munix : A Unique ID Generator

A unique random id generator that produces unique signed 64 bit integers without the need for a dedicated id server. The generated id's can be used as unique identifiers for objects. Collisions are guaranteed not to occur. This was built to run on one or several machines that accept multiple requests per second.

# Install

`composer install munix/munix`

# Requirements
Munix requires `sqlite` to be installed and enabled in `php.ini`. A small sqlite db it used to track number sequences within milliseconds of each other to prevent collisions. Yes, it's small. It just contains one table, one column, and one row (1 x 1).

# Usage

To get an id, you only need to call one method, `Munix\Munix::nextId(int $customId)`.

The only thing needed now is a custom number from 0 (inclusive) to 1023 (inclusive). For a single project, any number in that range will suffice. However, if it's a distributed system, each machine will need a different number, to avoid collisions.

If no `custom id` is supplied, the default value of `0` is used.

```php
<?php

use Munix\Munix;

// Using a custom id of 5
$id = Munix::nextId(5);

echo $id;
```

Will output

`85164824987754496`

To avoid passing the `custom id` each time you need an id, set one permanently by calling `Munix\Munix::setCustomId(int $customId)`.

```php
<?php

Munix::setCustomId(2);


Munix::nextId();

Munix::nextId();

```
## TLDR;
Just call the `nextId` method whenever you need a unique Id.

# Epoch
You can specify an epoch, in milliseconds by calling `Munix\Munix::setEpoch(int $timestampInMilliseconds)`.
The default epoch is `1640991600000`, Jan 1st, 2022.

```php
<?php

Munix::setEpoch(1640991600000);
Munix::setCustomId(1);

// Generate all you want
Munix::nextId();

Munix::nextId();

```

**The epoch should only be set once at the beginning of a project.**

# Contact
Twitter: @phrenotyper

Email: paul.contrib@gmail.com
