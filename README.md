# BHL volume parser

Scanned items in BHL usually have a string (called `VolumeInfo`) that describes the publication volume that was scanned. These strings are free-format and can be complicated, featuring annotaions on completeness of the scan, whether the scanned item comprises multiple volumes, date range, etc. In some cases a journal may have multiple numbering schemes, such as volume numbers that regularly reset at the start of a new series, but also volume or issue numbers that are consecuitive, independent of any series.

For example, `v.102=ser.3:v.32 (1876) [no.6206-6271]`.

A practical consequence of this is that anyone (or any tool) trying to locate an article based on a combination of journal name, volume, and starting page (e.g., [OpenURL](https://en.wikipedia.org/wiki/OpenURL)) is going to struggle. 

The code in this repository takes a list of all unique `VolumeInfo` values from the BHL data dump, and attempts to parse them into structured data. Volumes, issues, series, and dates are extracted and stored in a format close to CSL-JSON, but which allows for multiple values of volume, etc. Dates are stored as either single value, or a range from start and end.

The code was written using Claude, based on code I originally wrote that worked for a small set of examples.

## Examples

### v.101 (2004)

Trivial example of volume and year:

```json
{
  "text": "v.101 (2004)",
  "parsed": true,
  "volume": ["101"],
  "issued": { "date-parts": [[2004]] }
}
```


### v.31:pt.3-4 (1926-1927)

Issue as set of values, date as a range

```json
{
  "text": "v.31:pt.3-4 (1926-1927)",
  "parsed": true,
  "volume": ["31"],
  "issue": ["3", "4"],
  "issued": { "date-parts": [[1926], [1927]] }
}
```

### v.108-109 (2011-2012)

Volume as set of distinct values

```json
{
  "text": "v.108-109 (2011-2012)",
  "parsed": true,
  "volume": ["108", "109"],
  "issued": { "date-parts": [[2011], [2012]] }
}
```

### Bd.24 (1910:Jan.-1911:Jan.) 

Dates with months

```json
{
  "text": "Bd.24 (1910:Jan.-1911:Jan.)",
  "parsed": true,
  "volume": ["24"],
  "issued": {
    "date-parts": [[1910, 1], [1911, 1]]
  }
}
```




## Special cases

These strings contain a lot of eccentricities, and the parser makes a number of deliberate choices about how to handle them, such as treating a lone `no.` as a volume. Those choices, together with the cases that are still unresolved, are listed in [SPECIAL-CASES.md](SPECIAL-CASES.md).
