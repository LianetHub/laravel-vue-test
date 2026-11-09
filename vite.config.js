import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import svgLoader from "vite-svg-loader";
import path from "path";

const cssDir = path.resolve("resources/css");

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/js/app.ts"],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        svgLoader({
            svgoConfig: {
                plugins: [
                    {
                        name: "preset-default",
                        params: {
                            overrides: {
                                removeViewBox: false,
                            },
                        },
                    },
                ],
            },
        }),
    ],
    resolve: {
        alias: {
            "@": path.resolve("resources/js"),
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                loadPaths: [cssDir],
                additionalData: (content, filepath) => {
                    const normalized = filepath.replace(/\\/g, "/");
                    if (normalized.includes("/resources/css/")) {
                        return content;
                    }

                    return `@use "variables" as *;\n@use "mixins" as *;\n${content}`;
                },
            },
        },
    },
});
