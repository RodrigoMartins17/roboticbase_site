    </main>
    <!-- Fim do conteúdo. Agora o rodapé, igual em todo o site (vem do dashboard). -->

    <style>
        .site-footer { padding: 4rem 0 2rem; text-align: center; background: #050505; border-top: 1px solid rgba(255,255,255,0.08); }
        .site-footer .logo-img { height: 70px; width: auto; object-fit: contain; margin-bottom: 1rem; }
        .site-footer .social-icons a { color: #fff; font-size: 1.4rem; margin: 0 10px; display: inline-block; transition: all 0.3s ease; }
        .site-footer .social-icons a:hover { color: #0066ff; transform: translateY(-4px); }
        .site-footer .copyright { font-size: 0.85rem; color: #aaa; margin-top: 1.5rem; }
    </style>

    <footer class="site-footer">
        <div class="container">
            <a href="<?php echo BASE_URL; ?>dashboard/index" class="d-inline-block">
                <img src="<?php echo BASE_URL; ?>uploads/logosite.png" alt="Clube de Robótica" class="logo-img">
            </a>
            <div class="social-icons mb-2">
                <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                <a href="https://www.facebook.com/roboticaxl/" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="mailto:roboticaxl.aeffl@gmail.com" aria-label="Email"><i class="fas fa-envelope"></i></a>
            </div>
            <div class="copyright">&copy; <?php echo date('Y'); ?> Clube de Robótica · RoboticaXL — Escola Secundária Dr. Francisco Fernandes Lopes</div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>js/rb-loading.js"></script>
</body>
</html>
