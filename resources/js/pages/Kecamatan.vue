<template>
    <div>
        <h1>Daftar Kecamatan</h1>
        <table>
            <thead>
                <tr>
                    <th>Kode Provinsi</th>
                    <th>Kode Kabupaten/Kota</th>
                    <th>Kode</th>
                    <th>Nama</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in kecamatan" :key="item.kode_provinsi + '-' + item.kode_kabupaten_kota + '-' + item.kode">
                    <td>{{ item.kode_provinsi }}</td>
                    <td>{{ item.kode_kabupaten_kota }}</td>
                    <td>{{ item.kode }}</td>
                    <td>{{ item.nama }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
defineProps({ kecamatan: Array });
</script>
