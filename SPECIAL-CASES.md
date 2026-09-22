# Special cases

Decisions the parser makes that aren't obvious from the code, and known gaps worth coming
back to. Counts are against the 109,844 records in `volumeinfo.txt`.

Run `php go.php` for current totals and `php go.php --failed` for the strings that don't
parse at all.

## Deliberate decisions

These are choices, not bugs. Changing any of them will move a lot of records, so check
`php tests.php` and compare output before and after.

**A lone `no.` becomes the volume.** `no.71 (2019)` gives `volume: ["71"]`, not an issue,
because a number is a journal's first level of splitting when there's nothing above it. If
a volume label appears anywhere in the record, `no.` becomes the issue instead — so
`no.53=v.7` gives volume 7, issue 53.

**One subdivision is always the issue, whatever it's labelled.** `v.28:pt.3-4` gives
`issue: ["3","4"]`. `part` only appears when a volume is split twice over
(`v.13:pt.5B:no.1`), which is 1,006 records. This keeps volume/issue matching stable for
the common case.

**Ranges are expanded into every value.** `no.1-3` gives `["1","2","3"]`. `volume` and
`issue` are lists of discrete values; `issued.date-parts` is a range with two endpoints and
is *not* expanded. So `1863-1913` gives two date-parts but 51 volume values.

**A year can be the volume.** For serials enumerated by year of publication, a bare leading
year becomes the volume as well as the date: `1886:pt.1` is volume 1886, issue 1. This does
not happen when a volume label outranks it (`1901:v.2` is volume 2) or when the record is
prose (see below).

**Unrecognised words suppress what follows.** `Box 3: Folder 2: Pinus` deliberately yields
no volume, rather than inventing volume 3 from a word the parser doesn't know. Adding a
label to `bhl_labels()` is what turns such a word into enumeration.

**Report codes go to `number`.** `PA-146`, `Technical note-20` and similar set
`number` rather than a volume — 659 records.

## Known gaps

### `sect.C` and other letter designations go to `issue` — ~100 records

`v.8:sect.C (1913)` gives `issue: ["C"]`. CSL-JSON has a proper `section` variable
(`string`), which would be a better home. Same shape affects `pt.C`, `pt.D` (14 records
where a single letter follows a volume or part label).

Note that single letters `I`, `V` and `X` *are* converted as roman numerals after a label,
while `L`, `C`, `D` and `M` are kept literal — `v. L` and `t.C` read far more like
designations than like 50 and 100. That split is in `bhl_tokenise()`.

### `supplement` is a boolean — 927 records

`$obj->supplement` is set to `true`. CSL-JSON types `supplement` as `string|number`, so
this is off-spec. Worse, a numbered supplement loses its number: `Suppl. 3 1917-33` sets
`supplement: true` and puts the 3 in `volume`, rather than `supplement: 3`.

### `YYYY-NN` is ambiguous — 660 whole-string, 81 embedded

`2018-67` is a report number; `1917-33` is the year range 1917–1933. The parser currently
expands a two-digit tail only when it lands within 10 years, which gets the 660
whole-string report numbers right but breaks the year ranges:

| string | current | should probably be |
|---|---|---|
| `2018-67` | volume 2018, issue 67, date 2018 | correct |
| `Suppl. 3 1917-33` | volume 3, issue 33, date 1917 | date 1917–1933, no issue |
| `1839-59` | volume 1839, issue 59, date 1839 | date 1839–1859, no issue |
| `Heft. I-II (1856-77)` | volume 2, issue 1, date 1856 | date 1856–1877 |
| `FHTET no.2008-06` | volume 2008 and 06 | `number`, as other report codes |

In each broken case the *start* year survives and the tail leaks into `issue`, so these
records look parsed and only the end of the range is missing.

Discriminators that look usable, none applied yet:

- a zero-padded tail (`-06`) is a report number, never a year
- a tail lower than the first year's last two digits (`2008-06`, `1987-06`) can't be a
  forward range
- first year before ~1900 is almost certainly a range, since the report-number series in
  this data are all modern

The guard is `bhl_expand_short_year()` plus the `<= 10` tests in
`bhl_collect_enumeration()` and `bhl_parse_chronology()`.

### Four or more levels collapse to three

`Jahrg.67:Bd.1-2:Heft.1-2:pt.1` gives volume 67, part 1–2, issue 1 — the Band and Heft both
land in `part` and one level is lost. A broad scan finds 128 records with three or more
subdivisions below the volume, though many are ranges with a repeated label
(`Part 6 No 1 - Part 6 No 1`) rather than genuine depth, so the real number is smaller.

Going further would mean an ordered list of levels rather than fixed `volume`/`part`/`issue`
fields.

### Records with a date but no enumeration — 2,909

`(1983:Jun)` and `Box 3: Folder 2: Pinus: 1875-1880` parse to a date only. For the bare
parenthesised ones the year *is* promoted to volume; for the archival ones it deliberately
isn't. Correct as far as it goes, but worth knowing these exist.

### Still unparsed — 1,379

Mostly archival folder listings, correspondent names (`Bowditch, J.H.`) and bare titles.
`php go.php --failed` lists them. A few are near misses:

- `ct.7 (1829)` — `ct.` isn't in the label vocabulary (8 records)
- `v.1:8-9(1908:Ag-S)` — `Ag` and `S` aren't recognised as August and September
- `Ser.2 - 3`, `ser. 5` — a series with nothing else parses, but yields no volume

### Corrupt ranges are left as endpoints — 12 records

`900-1987` and `045-1980` are almost certainly truncated `1900-1987` and `1945-1980`. They
exceed the 500-value sanity guard in `bhl_expand_range()` and come back as two endpoints
rather than a thousand values. Recovering the intended years would be guesswork.

### Sub-series names aren't unicode-normalised

`Quadrupèdes` appears under two spellings in `parallel[].collection-title` because the
source mixes precomposed and combining accents. Running titles through NFC would merge
them.
