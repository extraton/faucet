set -e

dir=$(pwd)

rm -rf ${dir}/build
git clone https://github.com/extraton/faucet.git ${dir}/build
cd ${dir}/build
git checkout "${1}"
cd front
yarn install
yarn run build
cd ${dir}/build
cp -R ${dir}/build/front/dist/* ${dir}/build/public/
rm -rf ./{.git,front}
docker build -f ../deploy/Dockerfile -t ghcr.io/extraton/faucet/faucet-worker:${1} .
docker push ghcr.io/extraton/faucet/faucet-worker:${1}
cd ../
rm -rf ${dir}/build
