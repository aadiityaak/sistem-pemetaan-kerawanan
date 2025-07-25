<template>
    <div>
        <h1>Daftar Kabupaten/Kota</h1>
        <table>
            <thead>
                <tr>
                    <th>Kode Provinsi</th>
                    <th>Kode</th>
                    <th>Nama</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in kabupatenKota" :key="item.kode_provinsi + '-' + item.kode">
                    <td>{{ item.kode_provinsi }}</td>
                    <td>{{ item.kode }}</td>
                    <td>{{ item.nama }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
defineProps({ kabupatenKota: Array });
</script>
