# AGENTS

## Purpose

This repository provides an attribute-driven projection layer for Symfony Serializer.

The core flow is:

1. A source class implements `IDCT\Mvc\Model\NormalizableInterface`.
2. The source class is annotated with `#[IDCT\Mvc\Attribute\DefaultViewProjection(...)]`.
3. `IDCT\Mvc\Normalizer\DefaultViewProjectionNormalizer` resolves the configured projection class.
4. The normalizer instantiates the projection and delegates back to Symfony Serializer.

Use this file as the project-specific guide for coding, testing, and review in this repository.

## Working In This Repository

- Keep changes small and focused.
- Preserve the explicit read-model approach of the library; do not introduce serializer-group-based alternatives into the core API.
- Prefer minimal API surface changes unless a task explicitly asks for broader redesign.
- Keep public examples aligned with the actual runtime behavior in `src/` and the executable examples in `tests/` and `features/`.
- Update documentation when behavior, contracts, or required setup changes.

## Repository Structure

- `src/Attribute/` contains the attribute contract used to map source classes to projection classes.
- `src/Model/` contains the marker interfaces used by the library.
- `src/Normalizer/` contains the runtime serializer integration.
- `tests/Unit/` contains narrow unit coverage for attributes, interfaces, and the normalizer.
- `tests/Integration/` contains serializer-level integration tests.
- `features/` contains Behat scenarios and fixtures that act as functional acceptance coverage.

## How The Symfony Serializer And Normalizer Work Here

The important runtime behavior is defined by `IDCT\Mvc\Normalizer\DefaultViewProjectionNormalizer`.

- It participates in the Symfony Serializer chain as a normalizer.
- It supports only objects implementing `NormalizableInterface`.
- It checks whether the source class has a `DefaultViewProjection` attribute.
- If present, it resolves the configured projection class and instantiates it with the source object.
- It then delegates normalization of that projection to the next normalizer in the serializer stack, typically Symfony's `ObjectNormalizer`.

In practice, this means the custom normalizer is an adapter layer in front of Symfony's normal object normalization.

Current implementation details that matter when changing code:

- Projection lookup uses reflection against the source class attribute.
- The normalizer keeps a positive-only in-memory cache of resolved projection classes.
- The normalizer implements `Symfony\Contracts\Service\ResetInterface`, so the cache can be cleared in long-running processes.
- The cache is an optimization for repeated successful lookups only; classes without the attribute are not retained.

When wiring serializers manually, `DefaultViewProjectionNormalizer` must appear before `ObjectNormalizer` in the normalizer list.

## Using The Library Correctly

When adding examples, tests, or new library behavior, preserve these assumptions:

- Source classes must implement `NormalizableInterface`.
- Projection classes must implement `ViewProjectionInterface`.
- `ViewProjectionInterface` currently requires `__construct(NormalizableInterface $source)`.
- The constructor requirement is only a minimum contract. It does not prove that a projection matches the exact attributed source class.
- Projection classes should narrow the incoming source object immediately when they depend on a concrete source type.

Do not document or imply stronger validation than the library currently performs.

## Testing Expectations

Before considering a change complete, run the relevant checks from `composer.json`.

Default validation sequence:

1. `composer run test:static`
2. `composer run test:unit`
3. `composer run test:feature`

Use narrower commands only when the change is intentionally scoped and you are iterating quickly, but finish with the full relevant set before closing work.

## Acceptance Checklist

Treat a change as acceptable only if all of the following are true:

1. The behavior is covered by tests at the right level.
2. Static analysis passes.
3. Public examples and README text still match actual behavior.
4. The change preserves the library's explicit projection-based design.
5. Any new error messages are clear and library-specific.

## Writing Unit Tests

Use `tests/Unit/` for narrow, behavior-focused coverage.

- Unit-test attributes, interfaces, and the normalizer in isolation.
- Prefer small inline fixture classes inside the test file when they are only relevant to that test.
- Verify one contract at a time: valid flow, invalid flow, cache behavior, reset behavior, interface requirements, and error messages.
- When testing invalid constructor or type scenarios, make the intent explicit so static analysis understands the negative test case.
- Keep mocks limited to serializer delegation points; do not over-mock simple value objects.

Good candidates for unit tests in this repository:

- attribute validation
- normalizer support checks
- normalizer delegation behavior
- cache and reset semantics
- interface-level contract changes

## Writing Functional Tests

This repository has two broader test layers.

### Integration Tests

Use `tests/Integration/` when you need to verify behavior through a real `Symfony\Component\Serializer\Serializer` instance without the full Behat layer.

- Build the serializer with `DefaultViewProjectionNormalizer` before `ObjectNormalizer`.
- Assert on final normalized arrays or serialized JSON.
- Use these tests for end-to-end serializer behavior that is still developer-focused and compact.

### Behat Functional Tests

Use `features/` when you want executable acceptance scenarios.

- Put user-observable normalization behavior in `features/normalization.feature`.
- Keep step definitions in `features/bootstrap/NormalizationContext.php` readable and close to domain language.
- Keep reusable test fixtures in `features/fixtures/`.
- Prefer adding or extending scenarios that describe output behavior, nested objects, collections, aliases, and calculated values.

Use Behat for acceptance-level behavior, not low-level implementation details.

## Documentation Expectations

When behavior changes, update at least the relevant parts of:

- `README.md`
- unit or integration examples if the public contract changed
- Behat scenarios if user-visible serialization behavior changed

Documentation should describe what the library actually guarantees today, not what it may guarantee after a future redesign.

## Change Discipline

- Avoid unrelated refactors while implementing a focused task.
- Do not change the serializer chain semantics unless the task explicitly requires it.
- Do not silently broaden the attribute or interface contracts without tests and docs.
- If adding new repository instructions later, keep this file as the main project guide and update `.github/copilot-instructions.md` only if the entry point needs to change.