        </div> <!-- end container-fluid -->
    </main>
    <footer class="footer mt-auto">
        <span>© UTeM Maintenance System <?php echo date("Y"); ?></span>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
<?php
if (isset($conn)) {
    $conn->close();
}
?>