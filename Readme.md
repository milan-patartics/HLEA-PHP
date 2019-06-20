# HLEA: High Level Encryption Algorithm
The **High Level Encryption Algorithm** (HLEA for reference) and this PHP implementation is 
licenced under the GNU Affero General Public License v3.

## About
HLEA is a next generational encryption algorithm to provide higher level of security compared to the 
current encryption standards.

The main reason behind the development of the HLEA is to create a robust and open access encryption 
standard, that brings secure information protection against currently used and future attacking 
techniques.

## Characteristics
HLEA is a symmetric, byte based encryption method that processes bytes in pairs. It uses a key based 
encryption algorithm and a cryptographically secure pseudo-random generator. However HLEA processes 
byte pairs, it is considered to be a stream cypher.

The encrypted data size is always `N`+1 bytes for even number of input bytes, and `N`+2 bytes for odd 
number of input bytes - where `N` is the number of bytes in the original data. It is necessary to add 
this 1 or 2 bytes of extra data to the output, because HLEA processes byte pairs, and if the input data 
has odd number of bytes, a new - cryptographically secure pseudo-random - byte is appended to the end of 
data before applying the encryption - to the last byte pair. The first byte of the encrypted data is a 
flag - 1 or 0 -, that indicates if a random byte has been appended to the end or not.

HLEA is designed to give false positive results for decrypting - the encrypted data - using a wrong key. 
This means if the somebody tries to decrypt the encrypted file with a wrong HLEA key, the algorithm 
will execute the decryption without any errors. However there will not be any errors, the decrypted data 
will be completely useless if wrong key is used.

The complexity of the - cryptographically secure, non-seeded random - encryption key is very high. With 
the default key settings, there are `256! x 256^306004 x 256! x 65536! x 65536^239954 x 65536!` possible 
versions!

HLEA algorithm has a linear runtime, so the required time to process the encryption/decryption is 
directly proportional to the size of the data.

## Explanation
HLEA is using a byte based encryption algorithm and it processes bytes in pairs. For easy understanding, 
people should become familiar with the encryption key structure first.

### Encryption Key Structure
HLEA processes data using an HLEA encryption key, which contains six data blocks:

##### Primary Byte Swap Table
The primary byte swap table is an array of all possible - unsigned - byte values (0-255), shuffled 
with the Fisher-Yates algorithm using a cryptographically secure pseudo-random generator.

##### Byte Stream
The byte stream contains `N1` pieces of cryptographically secure pseudo-random bytes.

##### Secondary Byte Swap Table
The secondary byte swap table is similar to the primary byte swap table, it has the same structure, 
but it stores all possible - unsigned - byte values in a different random order.

##### Primary UInt16 Swap Table
The primary uint16 swap table is similar to the byte swap tables, it is an array of all possible - 
unsigned - 16 bit integer values (0-65535), shuffled with the Fisher-Yates algorithm using a 
cryptographically secure pseudo-random generator.

##### UInt16 Stream
The uint16 stream - similarly to the byte stream - contains `N2` pieces of - unsigned, and - 
cryptographically secure pseudo-random 16 bit integers.

##### Secondary UInt16 Swap Table
The secondary uint16 swap table is similar to the primary uint16 swap table, it has the same structure, 
but it stores all possible - unsigned - 16 bit integer values in a different random order.

### Algorithm
The HLEA algorithm uses six steps to process the data - both for encrypting and decrypting.

#### Encryption Process
Because the HLEA algorithm processes byte pairs, if the input data has odd number of bytes, a new - 
cryptographically secure pseudo-random - byte is appended to the end of the original data before 
applying the encryption. When this happens, the algorithm returns a flag about this. This flag is used 
in the decryption process to drop that last random byte after decrypting the data.

##### Primary Byte Swapping
The first step is to swap - change - the value of the given byte using the primary byte swap table. 
It means, that if `B` is the byte to swap, then it will have a new value of the `B`-th element in the 
primary byte swapping table. So basically `B`'s value is used as an index the get the primary 
swapped value from the primary byte swap table.

##### Byte Adjusting
In this seconds step, the algorithm will get a byte value from the byte stream to use it as the 
adjusting byte. To identify the proper byte in the byte stream, it uses the original byte's 
index in it's context - it's position in the original data. To handle cases when the original byte's 
index is greater than the index of the last byte in the byte stream, the adjusting byte index is 
calculated as the followings:

`adjustingByteIndex = originalByteIndex % lengthOfByteStream`

So the adjusting byte is the adjustingByteIndex-th byte in the byte stream. To calculate the adjusted 
byte, the algorithm applies the following formula:

`adjustedByte = primarySwappedByte + adjustingByte`

This way the adjustedByte may have a value which is greater than 255. If this happens, it will be 
decreased by 256.

##### Secondary Byte Swapping
In this third step, the adjustedByte from the previous step will be swapped using the secondary byte 
swap table, exactly the same way as in the first step, but with the secondary byte swap table instead 
of the primary byte swap table.

##### Primary UInt16 Swapping
In this fourth step, after applying the previous steps to both bytes, the algorithm takes the two - 
secondary swapped - bytes and gets the 16 bit unsigned integer representation of them - using little 
endian byte order. Then similarly to the very first step, this uint16 is swapped by the primary uint16 
swapping table.

##### UInt16 Adjusting
In this fifth step, the primary swapped uint16 from the previous step is getting adjusted by the proper
uint16 of the uint16 stream - very similarly to the byte adjusting step. Because two bytes was used to 
define the uint16, to find the proper index of the adjusting uint16 in the uint16 stream, the current 
byte pair's first element's index need to be divided by 2. So the adjusting uint16 index is calculated 
as the followings:

`adjustingUInt16Index = (indexOfTheFirstByteOfTheBytePair / 2) % lengthOfUInt16Stream`

This way the adjusting uint16 is the adjustingUInt16Index-th uint16 in the uint16 stream. To calculate 
the adjusted uint16, the algorithm applies the following formula:

`adjustedUInt16 = primarySwappedUInt16 + adjustingUInt16`

This way the adjustedUInt16 may have a value which is greater than 65535. If this happens, it will be 
decreased by 65536.

##### Secondary UInt16 Swapping
In this last - sixth - step, the adjustedUInt16 from the previous step is swapped using the secondary 
uint16 swap table. After the swapping, the algorithm takes the byte pair representation of the secondary 
swapped uint16 - using little endian byte order. This byte pair is the final - encrypted - byte pair.

After this last step the algorithm finished the encryption process of the given byte pair.

#### Decryption Process
The decryption process of the HLEA is the exact inverse of the encryption process.

##### Reverse Secondary UInt16 Swapping
In this first step, the algorithm takes the byte pair's unsigned 16 bit integer representation - using 
little endian byte order -, and executes a reverse swap on this uint16 using the secondary uint16 swap 
table. Reverse swapping is very similar to the swapping process used during the encryption, but instead 
of returning a value by index, it returns an index by the given value.

##### Reverse UInt16 Adjusting
In this second step, the reverse secondary swapped uint16 is reverse adjusted by the proper value of the 
uint16 stream. To identify the adjustingUInt16Index, the algorithm uses the same formula as in uint16 
adjusting step of the encryption process. To get the reverse adjusted uint16 value, the following formula 
is applied:

`reverseAdjustedUInt16 = adjustedUInt16 - adjustingUInt16`

This way the reverseAdjustedUInt16 may have a negative value, so if this happens, it will be increased 
by 65536.

##### Reverse Primary UInt16 Swapping
In this third step, the algorithm executes the uint16 reverse swapping on the uint16 value from the 
previous step using the primary uint16 swap table.

##### Reverse Secondary Byte Swapping
In this fourth step, the algorithm takes the byte pair representation of the uint16 from the previous 
step - using little endian byte order. After this, it reverse swaps the byte values using the secondary 
byte swap table.

##### Reverse Byte Adjusting
In this fifth step, the byte value is reverse adjusted by the proper byte of the byte stream. To 
identify the proper adjustingByteIndex, the algorithm uses the same formula as in byte adjusting 
step of the encryption process. To get the reverse adjusted byte value, the following formula is 
applied:

`reverseAdjustedByte = adjustedByte - adjustingByte`

This way the reverseAdjustedByte may have a negative value, so if this happens, it will be increased 
by 256.

##### Reverse Primary Byte Swapping
In this last - sixth - step, the algorithm executes a reverse swap on the byte value using the primary 
byte swapping table.

After this last step the algorithm finished the decryption process of the given byte pair.

## PHP Implementation
This PHP implementation of HLEA is written in PHP 7, without using any third-party solutions.

However it is a fully featured, working version of the HLEA algorithm, it's main goal is to provide a 
platform independent, well documented and easy to understand source code, that can be run on most 
computer platforms without installing any complex runtime environments or compilers.

Because of this reasons, this solution is much slower than it could be. It is not optimized for 
performance, but to provide a base and research material for further developments.

This PHP implementation comes with examples (_Examples_) and with a CLI (_Commands_) that can 
be used to access main features from a terminal.

The usage of the CLI commands is very simple. Users can run a terminal in the _Commands_ folder - or use 
the `cd` command to navigate to it. There are three command available:

 - `generate-hlea-key`: generates a new HLEA key file with the default settings.
 - `hlea-encrypt-file`: encrypts a file using an HLEA key file and saves the results.
 - `hlea-decrypt-file`: decrypts an encrypted file using an HLEA key file and saves the results.

### Examples of using the CLI commands:

#### Linux
`php generate-hlea-key output="/home/user/key.hleakey"`\
`php hlea-encrypt-file input="/home/user/file.dat" key="/home/user/key.hleakey" output="/home/user/file.dat.hleafile"`\
`php hlea-decrypt-file input="/home/user/file.dat.hleafile" key="/home/user/key.hleakey" output="/home/user/file.decrypted.dat"`

#### MacOS
`php generate-hlea-key output="/Users/user/Documents/key.hleakey"`\
`php hlea-encrypt-file input="/Users/user/Documents/file.dat" key="/Users/user/Documents/key.hleakey" output="/Users/user/Documents/file.dat.hleafile"`\
`php hlea-decrypt-file input="/Users/user/Documents/file.dat.hleafile" key="/Users/user/Documents/key.hleakey" output="/Users/user/Documents/file.decrypted.dat"`

#### Windows
`php generate-hlea-key output="C:\Users\user\Documents\key.hleakey"`\
`php hlea-encrypt-file input="C:\Users\user\Documents\file.dat" key="C:\Users\user\Documents\key.hleakey" output="C:\Users\user\Documents\file.dat.hleafile"`\
`php hlea-decrypt-file input="C:\Users\user\Documents\file.dat.hleafile" key="C:\Users\user\Documents\key.hleakey" output="C:\Users\user\Documents\file.decrypted.dat"`

## Author Info
The **High Level Encryption Algorithm** (HLEA) is developed by 
[Milan Patartics](mailto:milan@patartics.com), in 2019.