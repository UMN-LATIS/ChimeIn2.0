# K6 Load Tests

To run the K6 load tests, you need to have K6 installed on your system:

```bash
brew install k6
```

## Echo Load Test

```bash
export JOIN_CODE=123456 
k6 run ./tests/k6/echo-load-test.ts
```

## Reverb Load Test

```bash
export JOIN_CODE=123456
export REVERB_APP_KEY=your_reverb_app_key
k6 run ./tests/k6/reverb-load-test.ts
```
