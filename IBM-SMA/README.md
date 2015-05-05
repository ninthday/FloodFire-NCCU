# Data Fetcher
## Publish data fetcher

```
# create data fetcher
cd /ibm/sma13/cci_datafetcher/DataFetcher_SDK_1.3.0/
(remote scp news-fetcher folder into it)

# publish
cd /ibm/sma13/cci_installmgr/cci_mngmt/cci_cli
./cci_cli.sh -process publish
(type 'y')
(type '/ibm/sma13/cci_datafetcher/DataFetcher_SDK_1.3.0/news-fetcher-AppleDaily/news-fetcher-AppleDaily.dfm')

# unpublish
./cci_cli.sh -process unpublish datafetcherId news-fetcher-AppleDaily
```

### FAQ

 * the 'Language' field is required, not optional!
