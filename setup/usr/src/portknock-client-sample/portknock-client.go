package main

import (
  "bytes"
	"crypto/aes"
	"crypto/sha256"
	"crypto/cipher"
	"crypto/md5"
	"encoding/base64"
  "encoding/hex"
	"flag"
	"fmt"
	"net"
	"net/http"
	"regexp"
	"time"
)

func main() {
	var serverIP string
	var serverPort int
	var user string
	var password string
	var servicios string

	// Parse command-line arguments
	flag.StringVar(&serverIP, "serverIP", "servidor.issabel", "Server IP address")
	flag.IntVar(&serverPort, "serverPort", 12343, "Server port")
	flag.StringVar(&user, "user", "admin", "User")
	flag.StringVar(&password, "password", "password", "Password")
	flag.StringVar(&servicios, "servicios", "HTTPS,SSH", "Services")

	flag.Parse()

	tstamp := time.Now().Unix()
	externalContent, _ := http.Get("http://checkip.dyndns.com/")
	defer externalContent.Body.Close()
	buf := make([]byte, 1024)
	externalContent.Body.Read(buf)
	content := string(buf)
	re := regexp.MustCompile(`Current IP Address: \[?([:.0-9a-fA-F]+)\]?`)
	matches := re.FindStringSubmatch(content)
	externalIP := matches[1]

	data := fmt.Sprintf("%d:%s:%s", tstamp, externalIP, servicios)
	md5Password := GetMD5Hash(password)
	payload,err := GetAESEncrypted(data, md5Password)
  	if err != nil {
		fmt.Println("Encryption error:", err)
		return
	}

	fmt.Printf("Sending knock to IP %s, port %d for user %s, data %s, payload %s\n\n", serverIP, serverPort, user, data, payload)

	message := fmt.Sprintf("%s:%s", user, payload)

	conn, err := net.Dial("udp", fmt.Sprintf("%s:%d", serverIP, serverPort))
	if err != nil {
		fmt.Println("Error:", err)
		return
	}
	defer conn.Close()

	conn.Write([]byte(message))
}

func GetAESEncrypted(plaintext string, mkey string) (string, error) {
  key1, iv1, err := generateKeyAndIV(mkey)
  if err != nil {
    return "", err
  }
  key := fmt.Sprintf("%x", key1)
  iv := fmt.Sprintf("%x", iv1)

	var plainTextBlock []byte
	length := len(plaintext)

	if length%16 != 0 {
		extendBlock := 16 - (length % 16)
		plainTextBlock = make([]byte, length+extendBlock)
		copy(plainTextBlock[length:], bytes.Repeat([]byte{uint8(extendBlock)}, extendBlock))
	} else {
		plainTextBlock = make([]byte, length)
	}

	copy(plainTextBlock, plaintext)
	block, err := aes.NewCipher([]byte(key))

	if err != nil {
		return "", err
	}

	ciphertext := make([]byte, len(plainTextBlock))
	mode := cipher.NewCBCEncrypter(block, []byte(iv))
	mode.CryptBlocks(ciphertext, plainTextBlock)

	str := base64.StdEncoding.EncodeToString(ciphertext)

	return str, nil
}

// PKCS7 padding
func padPKCS7(data []byte, blockSize int) []byte {
	padding := blockSize - len(data)%blockSize
	padText := bytes.Repeat([]byte{byte(padding)}, padding)
	return append(data, padText...)
}

func GetMD5Hash(text string) string {
   hash := md5.Sum([]byte(text))
   return hex.EncodeToString(hash[:])
}

func generateKeyAndIV(text string) ([]byte, []byte, error) {

    // Hash the text to generate pseudorandom bytes
    hashed := sha256.Sum256([]byte(text))

    // Take the first 32 bytes for the key
    key := hashed[:aes.BlockSize]

    // Take 16 bytes for the IV
    iv := hashed[aes.BlockSize:aes.BlockSize+8]

    return key, iv, nil
}
