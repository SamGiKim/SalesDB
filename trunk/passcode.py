import time
import hmac
import hashlib

PASSCODE_PERIOD_MINUTES = 30
SECONDS_PER_PERIOD = 60 * PASSCODE_PERIOD_MINUTES

key_part1 = bytes([0x74, 0x61, 0x73, 0x6B])
key_part2 = bytes([0x71, 0x6F, 0x73])

def generate_passcode(period: int, key: bytes) -> int:
    period_bytes = period.to_bytes(8, byteorder='big')

    digest = hmac.new(key, period_bytes, hashlib.sha256).digest()
    code_raw = int.from_bytes(digest[:4], 'big')
    return (code_raw % 90000000) + 10000000

def assemble_key():
    return key_part1 + key_part2 

def main():
    now = int(time.time())
    period = now // SECONDS_PER_PERIOD
    secret_key = assemble_key()
    code = generate_passcode(period, secret_key)

    print(f'Generated Passcode: {code:08d}')
if __name__ == '__main__':
    main()
